<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Repositories;

use App\Domains\Intelligence\Security\Models\GatewayUsage;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class GatewayUsageRepository
{
    public function latest(int $limit = 100): Collection
    {
        return GatewayUsage::query()->latest('measured_at')->limit($limit)->get();
    }

    public function requestsSince(?string $clientId, CarbonInterface $since): int
    {
        return (int) GatewayUsage::query()
            ->when($clientId, fn ($query, string $id) => $query->where('gateway_client_id', $id))
            ->where('measured_at', '>=', $since)
            ->sum('request_count');
    }

    public function tokensToday(?string $clientId, CarbonInterface $since): int
    {
        return (int) GatewayUsage::query()
            ->when($clientId, fn ($query, string $id) => $query->where('gateway_client_id', $id))
            ->where('measured_at', '>=', $since)
            ->sum('total_tokens');
    }
}
