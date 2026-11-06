<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Auth\Http\Responses\Contracts\PasswordResetResponse;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class ResetPassword extends BaseResetPassword
{
    public function mount(?string $email = null, ?string $token = null): void
    {
        $this->token = $token ?? request()->query('token');

        $this->form->fill([
            'email' => $email ?? request()->query('email'),
        ]);
    }

    public function resetPassword(): ?PasswordResetResponse
    {
        $broker = Password::broker(Filament::getAuthPasswordBroker());
        $user = $broker->getUser(['email' => $this->email]);

        if (
            $user instanceof FilamentUser
            && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())
            && $this->token !== null
            && $broker->tokenExists($user, $this->token)
        ) {
            Notification::make()
                ->title('No tienes acceso al panel')
                ->body('Contacta con un administrador para que revise tu acceso.')
                ->danger()
                ->send();

            return null;
        }

        return parent::resetPassword();
    }
}
