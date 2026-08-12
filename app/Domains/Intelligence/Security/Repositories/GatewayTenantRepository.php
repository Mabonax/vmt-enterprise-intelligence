<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Repositories;

use App\Domains\Intelligence\Security\Models\GatewayTenant;
use Illuminate\Database\Eloquent\Collection;

class GatewayTenantRepository
{
    public function latest(int $limit = 50): Collection
    {
        return GatewayTenant::query()->latest()->limit($limit)->get();
    }

    public function findByOrganization(string $organizationId): ?GatewayTenant
    {
        return GatewayTenant::query()->where('organization_id', $organizationId)->first();
    }
}
