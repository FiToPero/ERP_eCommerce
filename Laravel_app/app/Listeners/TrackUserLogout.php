<?php

namespace App\Listeners;

use App\Models\UserSession;
use Illuminate\Auth\Events\Logout;

class TrackUserLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        $request = request();
        $sessionId = $request && $request->hasSession()
            ? $request->session()->getId()
            : null;

        $query = UserSession::query()
            ->where('user_id', $event->user->id)
            ->whereNull('logout_at')
            ->where('is_active', true);

        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        $activeSession = $query->latest('login_at')->first();

        if (! $activeSession) {
            return;
        }

        $activeSession->update([
            'logout_at' => now(),
            'last_activity_at' => now(),
            'logout_reason' => 'logout',
            'is_active' => false,
        ]);
    }
}
