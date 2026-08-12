<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Policies;

use App\Domains\Intelligence\Security\Models\GatewayTenant;
use App\Models\User;

class GatewayTenantPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user !== null;
    }

    public function manage(?User $user, ?GatewayTenant $tenant = null): bool
    {
        return $user !== null;
    }
}
