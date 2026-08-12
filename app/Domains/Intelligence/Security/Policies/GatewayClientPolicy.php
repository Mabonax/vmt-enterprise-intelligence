<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Policies;

use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Models\User;

class GatewayClientPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    public function manage(?User $user, ?GatewayClient $client = null): bool
    {
        return $user !== null;
    }
}
