<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeMemory;

class MemoryConsolidationService
{
    public function consolidate(): int
    {
        $grouped = KnowledgeMemory::query()->get()->groupBy(fn (KnowledgeMemory $memory): string => sha1($memory->summary.'|'.$memory->content));
        $removed = 0;

        foreach ($grouped as $memories) {
            if ($memories->count() <= 1) {
                continue;
            }

            $keeper = $memories->shift();

            foreach ($memories as $duplicate) {
                $keeper->update(['usage_count' => $keeper->usage_count + $duplicate->usage_count]);
                $duplicate->delete();
                $removed++;
            }
        }

        return $removed;
    }
}
