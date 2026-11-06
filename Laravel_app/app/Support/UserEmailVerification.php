<?php

namespace App\Support;

use App\Models\User;
use Filament\Auth\Notifications\VerifyEmail;
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
}
