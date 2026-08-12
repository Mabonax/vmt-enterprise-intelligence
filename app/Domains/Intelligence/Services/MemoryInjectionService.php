<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use Illuminate\Support\Collection;

class MemoryInjectionService
{
    public function inject(Collection $memories): string
    {
        return $memories
            ->map(static fn ($memory): string => sprintf(
                '[%s/%s] %s',
                $memory->memory_type->value,
                $memory->visibility->value,
                $memory->content,
            ))
            ->implode("\n");
    }
}
