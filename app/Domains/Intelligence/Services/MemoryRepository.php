<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\SemanticMemory;
use Illuminate\Database\Eloquent\Collection;

class MemoryRepository
{
    public function create(array $attributes): SemanticMemory
    {
        return SemanticMemory::query()->create($attributes);
    }

    public function forSubject(string $subjectType, string $subjectId): Collection
    {
        return SemanticMemory::query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->latest()
            ->get();
    }
}
