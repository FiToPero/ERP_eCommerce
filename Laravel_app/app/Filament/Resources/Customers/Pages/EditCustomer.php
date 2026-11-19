<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Support\CustomerPasswordBroker;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Components\Attributes\Exposed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Password;
use Throwable;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected ?bool $hasDatabaseTransactions = true;

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

    public function confirmEmailChangeAction(): Action
    {
        return Action::make('confirmEmailChange')
            ->requiresConfirmation()
            ->modalHeading('Confirmar cambio de correo')
            ->modalDescription('El nuevo correo quedará sin verificar y se enviará un enlace de verificación.')
            ->modalSubmitActionLabel('Confirmar y guardar')
            ->action(function (array $arguments): void {
                $customer = $this->getRecord()->fresh();
                abort_unless($customer && CustomerResource::canEdit($customer), 403);

                if ($customer->trashed() || $customer->email !== ($arguments['originalEmail'] ?? null)
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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->fill($data);
        $emailChanged = $record->isDirty('email');

        if ($emailChanged) {
            $record->email_verified_at = null;
        }

        $record = parent::handleRecordUpdate($record, $data);

        if ($emailChanged) {
            $record->getConnection()->afterCommit(fn () => $this->sendVerificationEmail($record));
        }

        return $record;
    }

    private function sendVerificationEmail(Customer $customer): void
    {
        try {
            $customer->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('No se pudo enviar el correo de verificación')
                ->body('El cliente sigue sin verificar. Comprueba la configuración de correo y vuelve a enviarlo.')
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->title('Correo de verificación enviado a '.$customer->email)
            ->success()
            ->send();
    }

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
                ->modalDescription('Se enviará el enlace al correo guardado. Guarda primero los cambios del formulario.')
                ->action(function (): void {
                    $customer = $this->getRecord()->fresh();
                    abort_unless($customer && CustomerResource::canEdit($customer), 403);

                    if ($customer->trashed() || $customer->hasVerifiedEmail()) {
                        Notification::make()->title('El cliente no necesita verificación')->warning()->send();

                        return;
                    }

                    $this->sendVerificationEmail($customer);
                }),
            Action::make('sendPasswordReset')
                ->label('Enviar enlace para restablecer contraseña')
                ->visible(fn (): bool => ! $this->getRecord()->trashed())
                ->requiresConfirmation()
                ->modalDescription('Se usará el correo y la web guardados. Guarda los cambios antes de enviar el enlace.')
                ->action(function (): void {
                    $customer = $this->getRecord()->fresh();
                    abort_unless($customer && CustomerResource::canEdit($customer), 403);
                    abort_if($customer->trashed(), 403);

                    try {
                        $status = app(CustomerPasswordBroker::class)->sendResetLink([
                            'email' => $customer->email,
                            'web' => $customer->web,
                        ]);
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('No se pudo enviar el enlace')
                            ->body('Comprueba la web del cliente y la configuración de correo.')
                            ->danger()
                            ->send();

                        return;
                    }

                    if ($status !== Password::RESET_LINK_SENT) {
                        Notification::make()->title('No se pudo enviar el enlace')->body(__($status))->danger()->send();

                        return;
                    }

                    Notification::make()->title('Enlace de restablecimiento enviado')->success()->send();
                }),
        ];
    }
}
