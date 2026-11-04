<?php

namespace App\Console\Commands;

use App\Models\CustomerSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloseStaleCustomerSessions extends Command
{
    protected $signature = 'customer-sessions:close-stale';

    protected $description = 'Close customer sessions whose Sanctum token expired or no longer exists';

    public function handle(): int
    {
        // session_id guarda el id del token en personal_access_tokens
        $staleIds = DB::table('customer_sessions')
            ->leftJoin('personal_access_tokens', 'customer_sessions.session_id', '=', 'personal_access_tokens.id')
            ->whereNull('customer_sessions.logout_at')
            ->where('customer_sessions.is_active', true)
            ->where(function ($query) {
                $query->whereNull('personal_access_tokens.id')
                    ->orWhere('personal_access_tokens.expires_at', '<', now());
            })
            ->pluck('customer_sessions.id');

        if ($staleIds->isEmpty()) {
            $this->info('No stale customer sessions found.');

            return self::SUCCESS;
        }

        CustomerSession::query()
            ->whereIn('id', $staleIds)
            ->update([
                'logout_at' => now(),
                'last_activity_at' => now(),
                'logout_reason' => 'expired',
                'is_active' => false,
                'updated_at' => now(),
            ]);

        $this->info('Closed ' . $staleIds->count() . ' stale customer sessions.');

        return self::SUCCESS;
    }
}
