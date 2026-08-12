<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Repositories;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeMemory;

class KnowledgeMemoryRepository
{
    public function create(array $attributes): KnowledgeMemory
    {
        return KnowledgeMemory::query()->create($attributes);
    }
}
