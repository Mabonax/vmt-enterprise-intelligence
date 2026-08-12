<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Repositories;

use App\Domains\Intelligence\Commercial\Models\IntelligenceSubscription;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRepository
{
    public function latest(int $limit = 25): Collection
    {
        return IntelligenceSubscription::query()->latest()->limit($limit)->get();
    }
}