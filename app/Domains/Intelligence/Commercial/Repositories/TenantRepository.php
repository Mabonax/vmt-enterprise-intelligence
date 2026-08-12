<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Repositories;

use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use Illuminate\Database\Eloquent\Collection;

class TenantRepository
{
    public function latest(int $limit = 25): Collection
    {
        return IntelligenceTenant::query()->latest()->limit($limit)->get();
    }
}