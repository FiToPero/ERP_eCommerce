<?php

namespace App\Console\Commands;

use App\Models\UserSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloseStaleUserSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:close-stale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close stale user sessions that expired or no longer exist in session storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoffTimestamp = now()->subMinutes((int) config('session.lifetime'))->timestamp;

        $staleIds = DB::table('user_sessions')
            ->leftJoin('sessions', 'user_sessions.session_id', '=', 'sessions.id')
            ->whereNull('user_sessions.logout_at')
            ->where('user_sessions.is_active', true)
            ->where(function ($query) use ($cutoffTimestamp) {
                $query->whereNull('sessions.id')
                    ->orWhere('sessions.last_activity', '<', $cutoffTimestamp);
            })
            ->pluck('user_sessions.id');

        if ($staleIds->isEmpty()) {
            $this->info('No stale sessions found.');

            return self::SUCCESS;
        }

        UserSession::query()
            ->whereIn('id', $staleIds)
            ->update([
                'logout_at' => now(),
                'last_activity_at' => now(),
                'logout_reason' => 'expired',
                'is_active' => false,
                'updated_at' => now(),
            ]);

        $this->info('Closed ' . $staleIds->count() . ' stale user sessions.');

        return self::SUCCESS;
    }
}
