<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Repositories;

use App\Domains\Intelligence\Security\Enums\GatewayClientStatus;
use App\Domains\Intelligence\Security\Models\GatewayClient;
use Illuminate\Database\Eloquent\Collection;

class GatewayClientRepository
{
    public function latest(int $limit = 50): Collection
    {
        return GatewayClient::query()->with('tenant')->latest()->limit($limit)->get();
    }

    public function activeForTenant(string $tenantId): Collection
    {
        return GatewayClient::query()
            ->where('gateway_tenant_id', $tenantId)
            ->where('status', GatewayClientStatus::Active->value)
            ->get();
    }
}
