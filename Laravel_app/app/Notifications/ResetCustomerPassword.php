<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use InvalidArgumentException;

class ResetCustomerPassword extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        if (! $notifiable instanceof Customer || ! filter_var($notifiable->web, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('El cliente no tiene una web válida.');
        }

        $web = parse_url($notifiable->web);
        if (! in_array($web['scheme'] ?? null, ['http', 'https'], true)
            || empty($web['host']) || isset($web['user']) || isset($web['pass'])
            || isset($web['query']) || isset($web['fragment'])
            || ! in_array($web['path'] ?? '', ['', '/'], true)) {
            throw new InvalidArgumentException('La web del cliente debe ser un origen HTTP o HTTPS.');
        }

        $url = rtrim($notifiable->web, '/').'/reset-password?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->email,
        ], '', '&', PHP_QUERY_RFC3986);

        return (new MailMessage)
            ->subject(__('Reset Password Notification'))
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $url)
            ->line(__('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.customers.expire')]))
            ->line(__('If you did not request a password reset, no further action is required.'));
    }
}
