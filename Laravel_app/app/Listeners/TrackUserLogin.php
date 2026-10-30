<?php

namespace App\Listeners;

use App\Events\PanelLoginCompleted;
use App\Models\UserSession;

class TrackUserLogin
{
    public function handle(PanelLoginCompleted $event): void
    {
        $existingActive = UserSession::query()
            ->where('user_id', $event->user->getAuthIdentifier())
            ->where('session_id', $event->sessionId)
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
            'user_id' => $event->user->getAuthIdentifier(),
            'session_id' => $event->sessionId,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent ?? '',
            'login_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
        ]);
    }
}
