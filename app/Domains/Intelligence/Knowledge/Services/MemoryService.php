<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

use App\Domains\Intelligence\Knowledge\Enums\KnowledgeMemoryType;
use App\Domains\Intelligence\Knowledge\Enums\KnowledgeVisibility;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeMemory;

class MemoryService
{
    public function remember(array $attributes): KnowledgeMemory
    {
        return KnowledgeMemory::query()->create(array_merge([
            'memory_type' => KnowledgeMemoryType::Document,
            'visibility' => KnowledgeVisibility::Organization,
            'importance' => 0.70,
            'confidence' => 0.70,
            'usage_count' => 0,
        ], $attributes));
    }
}
