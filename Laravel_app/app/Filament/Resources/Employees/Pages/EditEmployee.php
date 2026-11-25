<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use App\Support\UserEmailVerification;
use App\Support\UserRoleAssignments;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Components\Attributes\Exposed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    private ?Role $selectedRole = null;

    private bool $emailChanged = false;

    #[Exposed]
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $user = $this->getRecord()->user()->first();
        if ($user && ($this->data['user']['email'] ?? null) !== $user->email) {
            $this->mountAction('confirmEmailChange', [
                'email' => $this->data['user']['email'] ?? null,
                'originalEmail' => $user->email,
                'userId' => $user->getKey(),
                'shouldRedirect' => $shouldRedirect,
                'shouldSendSavedNotification' => $shouldSendSavedNotification,
            ]);

            return;
        }

        parent::save($shouldRedirect, $shouldSendSavedNotification);
    }

    public function confirmEmailChangeAction(): Action
    {
        return Action::make('confirmEmailChange')
            ->requiresConfirmation()
            ->modalHeading('Confirmar cambio de correo')
            ->modalDescription('El nuevo correo quedará sin verificar y se enviará un enlace de verificación.')
            ->modalSubmitActionLabel('Confirmar y guardar')
            ->action(function (array $arguments): void {
                $employee = $this->getRecord()->fresh();
                abort_unless($employee && EmployeeResource::canEdit($employee), 403);
                $user = $employee->user()->first();
                abort_unless($user && app(UserRoleAssignments::class)->canManageUser(Filament::auth()->user(), $user), 403);

                if ($employee->trashed() || $user->trashed() || $user->getKey() !== ($arguments['userId'] ?? null)
                    || $user->email !== ($arguments['originalEmail'] ?? null)
                    || ($this->data['user']['email'] ?? null) !== ($arguments['email'] ?? null)) {
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

                $this->getRecord()->refresh();
                $this->fillForm();
                $this->rememberData();
            });
    }

    protected function beforeSave(): void
    {
        $user = $this->getRecord()->user()->first();
        if (! $user) {
            throw ValidationException::withMessages(['data.user.email' => 'El empleado no tiene un usuario asociado.']);
        }

        $assignments = app(UserRoleAssignments::class);
        $actor = Filament::auth()->user();
        abort_unless($assignments->canManageUser($actor, $user), 403);

        $this->selectedRole = $assignments->allowedRoles($actor) !== []
            ? $assignments->resolve($actor, $this->data['user']['role_id'] ?? null, $user, 'data.user.role_id')
            : null;
        $this->emailChanged = ($this->data['user']['email'] ?? null) !== $user->email;

        if ($this->emailChanged) {
            $user->forceFill(['email_verified_at' => null])->save();
        }
    }

    protected function afterSave(): void
    {
        $user = $this->getRecord()->user()->firstOrFail();
        if ($this->selectedRole) {
            $user->syncRoles($this->selectedRole);
        }

        if ($this->emailChanged) {
            DB::afterCommit(fn () => app(UserEmailVerification::class)->sendWithNotification($user));
        }
    }

    private function authorizedUser(): User
    {
        $employee = $this->getRecord()->fresh();
        abort_unless($employee && ! $employee->trashed() && EmployeeResource::canEdit($employee), 403);
        $user = $employee->user()->firstOrFail();
        abort_unless(app(UserRoleAssignments::class)->canManageUser(Filament::auth()->user(), $user), 403);

        return $user;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            Action::make('sendVerificationEmail')
                ->label('Reenviar correo de verificación')
                ->visible(fn (): bool => ! $this->getRecord()->trashed() && ($this->getRecord()->user?->hasVerifiedEmail() === false))
                ->requiresConfirmation()
                ->modalDescription('Se enviará el enlace a la dirección guardada. Si modificaste el correo, guarda los cambios primero.')
                ->action(function (): void {
                    $user = $this->authorizedUser();
                    if ($user->hasVerifiedEmail()) {
                        Notification::make()->title('El usuario no necesita verificación')->warning()->send();

                        return;
                    }

                    app(UserEmailVerification::class)->sendWithNotification($user);
                }),
            Action::make('sendPasswordReset')
                ->label('Enviar enlace para restablecer contraseña')
                ->visible(fn (): bool => ! $this->getRecord()->trashed() && $this->getRecord()->user !== null)
                ->requiresConfirmation()
                ->action(function (): void {
                    $user = $this->authorizedUser();
                    if (! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())) {
                        Notification::make()
                            ->title('El usuario no tiene acceso al panel')
                            ->body('Asigna un rol con acceso al panel antes de enviar el enlace.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $status = Password::broker(Filament::getAuthPasswordBroker())
                        ->sendResetLink(['email' => $user->email], function (User $user, string $token): void {
                            $notification = app(ResetPassword::class, ['token' => $token]);
                            $notification->url = Filament::getResetPasswordUrl($token, $user);
                            $user->notifyNow($notification);
                        });

                    if ($status !== Password::RESET_LINK_SENT) {
                        Notification::make()->title('No se pudo enviar el enlace')->body(__($status))->danger()->send();

                        return;
                    }

                    Notification::make()->title('Enlace de restablecimiento enviado')->success()->send();
                }),
        ];
    }
}
