<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeEvent;
use App\Domains\Intelligence\Knowledge\Services\MemoryConsolidationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class MemoryConsolidationJob implements ShouldQueue
{
    use Queueable;

    public function handle(MemoryConsolidationService $consolidation): void
    {
        $removed = $consolidation->consolidate();

        KnowledgeEvent::query()->create([
            'event_type' => 'knowledge.memory.consolidated',
            'subject_type' => 'knowledge_memory',
            'subject_id' => 'all',
            'payload' => ['removed_duplicates' => $removed],
            'metadata' => [],
        ]);
    }
}
