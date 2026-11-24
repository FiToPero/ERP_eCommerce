<?php

// Edita usuarios y perfiles de forma atómica y administra sus enlaces de acceso.

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\UserEmailVerification;
use App\Support\UserRoleAssignments;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Components\Attributes\Exposed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    /** Solicita confirmación antes de guardar un cambio de correo. */
    #[Exposed]
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $email = $this->data['email'] ?? null;

        if ($email !== $this->getRecord()->email) {
            $this->mountAction('confirmEmailChange', [
                'email' => $email,
                'originalEmail' => $this->getRecord()->email,
                'shouldRedirect' => $shouldRedirect,
                'shouldSendSavedNotification' => $shouldSendSavedNotification,
            ]);

            return;
        }

        parent::save($shouldRedirect, $shouldSendSavedNotification);
    }

    /** Confirma el cambio si el usuario y los datos siguen siendo válidos. */
    public function confirmEmailChangeAction(): Action
    {
        return Action::make('confirmEmailChange')
            ->requiresConfirmation()
            ->modalHeading('Confirmar cambio de correo')
            ->modalDescription('El nuevo correo quedará sin verificar y se enviará un enlace de verificación.')
            ->modalSubmitActionLabel('Confirmar y guardar')
            ->action(function (array $arguments): void {
                $user = $this->getRecord()->fresh();
                abort_unless($user && UserResource::canEdit($user), 403);
                abort_unless(app(UserRoleAssignments::class)->canManageUser(Filament::auth()->user(), $user), 403);

                if ($user->trashed() || $user->email !== ($arguments['originalEmail'] ?? null)
                    || ($this->data['email'] ?? null) !== ($arguments['email'] ?? null)) {
                    Notification::make()
                        ->title('No se pudo confirmar el cambio de correo')
                        ->body('Los datos cambiaron. Recarga la página y vuelve a intentarlo.')
                        ->danger()
                        ->send();

                    return;
                }

                parent::save(
                    $arguments['shouldRedirect'] ?? true,
                    $arguments['shouldSendSavedNotification'] ?? true,
                );

                if ($this->getRecord()->wasChanged('email')) {
                    $this->getRecord()->refresh();
                    $this->fillForm();
                    $this->rememberData();
                }
            });
    }

    /** Carga el rol actual en el formulario. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_id'] = $this->getRecord()->roles()->value('roles.id');

        return $data;
    }

    /** Actualiza usuario y rol, y programa la verificación si cambia el correo. */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Model {
            $actor = Filament::auth()->user();
            $assignments = app(UserRoleAssignments::class);
            abort_unless($assignments->canManageUser($actor, $record), 403);

            $role = array_key_exists('role_id', $data)
                ? $assignments->resolve($actor, $data['role_id'], $record)
                : null;
            unset($data['role_id']);

            $record->fill($data);
            $emailChanged = $record->isDirty('email');
            if ($emailChanged) {
                $record->email_verified_at = null;
            }

            $record = parent::handleRecordUpdate($record, $data);
            if ($role) {
                $record->syncRoles($role);
            }

            if ($emailChanged) {
                DB::afterCommit(fn () => $this->sendVerificationEmail($record));
            }

            return $record;
        });
    }

    /** Envía la verificación e informa explícitamente si falla. */
    private function sendVerificationEmail(User $user): void
    {
        try {
            app(UserEmailVerification::class)->send($user);
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('No se pudo enviar el correo de verificación')
                ->body('El correo no está verificado. Comprueba la configuración de correo y vuelve a enviarlo.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Correo de verificación enviado a '.$user->email)
            ->success()
            ->send();
    }

    /** Define las acciones de eliminación, restauración y envío de enlaces. */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
            Action::make('sendVerificationEmail')
                ->label('Reenviar correo de verificación')
                ->visible(fn (): bool => ! $this->getRecord()->trashed() && ! $this->getRecord()->hasVerifiedEmail())
                ->requiresConfirmation()
                ->modalDescription('Se enviará el enlace a la dirección guardada. Si modificaste el correo, guarda los cambios primero.')
                ->action(function (): void {
                    $user = $this->getRecord()->fresh();
                    abort_unless($user && UserResource::canEdit($user), 403);
                    abort_unless(app(UserRoleAssignments::class)->canManageUser(Filament::auth()->user(), $user), 403);

                    if ($user->trashed() || $user->hasVerifiedEmail()) {
                        Notification::make()
                            ->title('El usuario no necesita verificación')
                            ->warning()
                            ->send();

                        return;
                    }

                    $this->sendVerificationEmail($user);
                }),
            Action::make('sendPasswordReset')
                ->label('Enviar enlace para restablecer contraseña')
                ->requiresConfirmation()
                ->action(function (): void {
                    if (! $this->getRecord()->canAccessPanel(Filament::getCurrentOrDefaultPanel())) {
                        Notification::make()
                            ->title('El usuario no tiene acceso al panel')
                            ->body('Asigna un rol con acceso al panel antes de enviar el enlace.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $status = Password::broker(Filament::getAuthPasswordBroker())
                        ->sendResetLink(
                            ['email' => $this->getRecord()->email],
                            function (User $user, string $token): void {
                                $notification = app(ResetPassword::class, ['token' => $token]);
                                $notification->url = Filament::getResetPasswordUrl($token, $user);

                                $user->notifyNow($notification);
                            },
                        );

                    if ($status !== Password::RESET_LINK_SENT) {
                        Notification::make()
                            ->title('No se pudo enviar el enlace')
                            ->body(__($status))
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Enlace de restablecimiento enviado')
                        ->success()
                        ->send();
                }),
        ];
    }
}
