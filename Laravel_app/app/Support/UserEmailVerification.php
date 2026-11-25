<?php

namespace App\Support;

use App\Models\User;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class UserEmailVerification
{
    public function send(User $user): void
    {
        $notification = app(VerifyEmail::class);
        $notification->url = URL::temporarySignedRoute(
            'admin-user.verify-email',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())],
        );

        $user->notifyNow($notification);
    }

    public function sendWithNotification(User $user): void
    {
        try {
            $this->send($user);
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
}
