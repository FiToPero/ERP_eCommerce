<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Throwable;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function afterCreate(): void
    {
        /** @var Customer $customer */
        $customer = $this->getRecord();

        $customer->getConnection()->afterCommit(function () use ($customer): void {
            try {
                $customer->sendEmailVerificationNotification();
            } catch (Throwable $exception) {
                report($exception);

                Notification::make()
                    ->title('Cliente creado, pero no se pudo enviar el correo de verificación')
                    ->body('Comprueba la configuración de correo y vuelve a enviarlo desde la tienda.')
                    ->danger()
                    ->persistent()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Cliente creado y correo de verificación enviado')
                ->success()
                ->send();
        });
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }
}
