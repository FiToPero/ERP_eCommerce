<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\RequestWeb;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given email.
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $web = RequestWeb::origin($request);

        // Sin origen o sin customer en esa web: misma respuesta que un email desconocido
        if (! $web) {
            return response()->json(['message' => __(Password::INVALID_USER)], 422);
        }

        $status = Password::broker('customers')->sendResetLink([
            'email' => $request->input('email'),
            'web'   => $web,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 422);
        }

        return response()->json(['message' => __($status)]);
    }

    /**
     * Reset the user's password using the token from the email link.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $web = RequestWeb::origin($request);

        if (! $web) {
            return response()->json(['message' => __(Password::INVALID_USER)], 422);
        }

        $status = Password::broker('customers')->reset(
            [
                ...$request->only('email', 'password', 'password_confirmation', 'token'),
                'web' => $web,
            ],
            function ($user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 422);
        }

        return response()->json(['message' => __($status)]);
    }
}

