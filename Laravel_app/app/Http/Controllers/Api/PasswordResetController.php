<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $status = Password::sendResetLink($request->only('email'));

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

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
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


// <?php

// namespace App\Http\Controllers\API;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Password;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Auth\Events\PasswordReset;
// use Illuminate\Support\Str;

// class CustomerPasswordController extends Controller
// {
//     // ENVIAR ENLACE DE RECUPERACIÓN AL CORREO
//     public function sendResetLink(Request $request)
//     {
//         $request->validate(['email' => 'required|email']);

//         // Forzamos a Laravel a usar el broker 'customers' de config/auth.php
//         $status = Password::broker('customers')->sendResetLink(
//             $request->only('email')
//         );

//         // Si el estado es exitoso, Laravel ya envió el correo usando tus vistas de notificación por defecto
//         if ($status === Password::RESET_LINK_SENT) {
//             return response()->json(['message' => 'Te hemos enviado el enlace de recuperación a tu correo.']);
//         }

//         // Si falla (por ejemplo, el correo no existe en la tabla de customers)
//         return response()->json([
//             'message' => 'No pudimos enviar el correo electrónico.',
//             'error' => __($status)
//         ], 400);
//     }

//     // PROCESAR EL CAMBIO FÍSICO DE CONTRASEÑA
//     public function resetPassword(Request $request)
//     {
//         $request->validate([
//             'token' => 'required', // El token largo que llegó por correo
//             'email' => 'required|email',
//             'password' => 'required|string|min:8|confirmed',
//         ]);

//         // Volvemos a invocar al broker 'customers' para validar todo el flujo
//         $status = Password::broker('customers')->reset(
//             $request->only('email', 'password', 'password_confirmation', 'token'),
//             function ($customer, $password) {
//                 // Esta función se ejecuta solo si el token y el email son válidos
//                 $customer->forceFill([
//                     'password' => Hash::make($password),
//                     'remember_token' => Str::random(60),
//                 ])->save();

//                 event(new PasswordReset($customer));
//             }
//         );

//         // Si todo salió bien
//         if ($status === Password::PASSWORD_RESET) {
//             return response()->json(['message' => 'Tu contraseña ha sido restablecida con éxito.']);
//         }

//         // Si el token expiró, es inválido o los datos no coinciden
//         return response()->json([
//             'message' => 'No se pudo restablecer la contraseña.',
//             'error' => __($status)
//         ], 400);
//     }
// }
