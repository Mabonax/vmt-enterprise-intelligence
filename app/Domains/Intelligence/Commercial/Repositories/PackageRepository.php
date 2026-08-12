<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Repositories;

use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use Illuminate\Database\Eloquent\Collection;

class PackageRepository
{
    public function latest(int $limit = 25): Collection
    {
        return IntelligencePackage::query()->latest()->limit($limit)->get();
    }
}