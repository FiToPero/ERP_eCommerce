<?php

namespace App\Listeners;

use App\Models\UserSession;
use Illuminate\Auth\Events\Login;

class TrackUserLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $request = request();

        if (! $request || ! $request->hasSession()) {
            return;
        }

        $sessionId = $request->session()->getId();

        if (! $sessionId) {
            return;
        }

        $existingActive = UserSession::query()
            ->where('user_id', $event->user->id)
            ->where('session_id', $sessionId)
            ->whereNull('logout_at')
            ->first();

        if ($existingActive) {
            $existingActive->update([
                'last_activity_at' => now(),
                'is_active' => true,
            ]);

            return;
        }

        UserSession::create([
            'user_id' => $event->user->id,
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'login_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);
    }
}
