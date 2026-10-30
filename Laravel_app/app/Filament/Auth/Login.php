<?php

namespace App\Filament\Auth;

use App\Events\PanelLoginCompleted;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response === null) {
            return null;
        }

        $request = request();
        $user = Filament::auth()->user();

        if ($user) {
            event(new PanelLoginCompleted(
                user: $user,
                sessionId: $request->session()->getId(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            ));
        }

        return $response;
    }
}