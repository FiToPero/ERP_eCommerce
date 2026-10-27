<?php

namespace App\Listeners;

use App\Models\UserSession;
use Illuminate\Auth\Events\Login;

class TrackUserLogin
{
// Laravel llama a este método cuando se dispara el evento Login (autenticación correcta).
// $event contiene el usuario autenticado en $event->user y el nombre del guard.

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Obtiene la petición HTTP actual con el helper global request().
        $request = request();

        // Sale si no hay petición (por ejemplo, un comando de consola o un job)
        // o si la petición no tiene sesión asociada. Sin sesión no hay ID que registrar.
        if (! $request || ! $request->hasSession()) {
            return;
        }

        // Ignora las rutas que empiezan por "api/". Los logins de Vue con token Sanctum
        // los registra AuthController, así que aquí se evitan filas duplicadas.
        if ($request->is('api/*')) {
            return;
        }

        // Lee el ID de la sesión web actual. En el evento Login este ID ya fue
        // regenerado por Laravel, por lo que es el definitivo (el mismo de la tabla `sessions`).
        $sessionId = $request->session()->getId();

        // Protección extra: si por alguna razón el ID viene vacío, no registra nada.
        if (! $sessionId) {
            return;
        }

        // Busca en `user_sessions` si ya existe una fila abierta para este mismo
        // usuario y este mismo ID de sesión:
        $existingActive = UserSession::query()
            ->where('user_id', $event->user->id)   // pertenece a este usuario
            ->where('session_id', $sessionId)      // con este ID de sesión
            ->whereNull('logout_at')               // y que aún no se haya cerrado
            ->first();                             // devuelve la primera coincidencia o null

        // Si ya existía, no crea otra fila: solo la refresca.
        if ($existingActive) {
            $existingActive->update([
                'last_activity_at' => now(), // actualiza la última actividad a este momento
                'is_active' => true,         // la mantiene marcada como activa
            ]);

            // Termina aquí para no ejecutar la creación de abajo.
            return;
        }

        // Si no existía, crea el registro de la nueva sesión.
        UserSession::create([
            'user_id' => $event->user->id,                  // usuario que inició sesión
            'session_id' => $sessionId,                     // ID de sesión de Laravel
            'ip_address' => $request->ip(),                 // IP del cliente
            'user_agent' => (string) $request->userAgent(), // navegador; (string) evita null
            'login_at' => now(),                            // fecha y hora del inicio de sesión
            'last_activity_at' => now(),                    // al inicio coincide con login_at
            'is_active' => true,                            // sesión marcada como activa
        ]);
    }
}
