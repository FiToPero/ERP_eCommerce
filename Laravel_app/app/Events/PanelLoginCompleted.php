<?php

namespace App\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class PanelLoginCompleted
{
    public function __construct(
        public readonly Authenticatable $user,
        public readonly string $sessionId,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
    ) {}
}