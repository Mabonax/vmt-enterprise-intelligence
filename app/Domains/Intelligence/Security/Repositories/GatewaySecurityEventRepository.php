<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Repositories;

use App\Domains\Intelligence\Security\Models\GatewaySecurityEvent;
use Illuminate\Database\Eloquent\Collection;

class GatewaySecurityEventRepository
{
    public function latest(int $limit = 100): Collection
    {
        return GatewaySecurityEvent::query()->latest('occurred_at')->limit($limit)->get();
    }
}
