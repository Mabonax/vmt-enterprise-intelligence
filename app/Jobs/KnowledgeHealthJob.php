<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeEvent;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeHealthService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class KnowledgeHealthJob implements ShouldQueue
{
    use Queueable;

    public function handle(KnowledgeHealthService $health): void
    {
        KnowledgeEvent::query()->create([
            'event_type' => 'knowledge.health.calculated',
            'subject_type' => 'knowledge_health',
            'subject_id' => 'summary',
            'payload' => $health->summary(),
            'metadata' => [],
        ]);
    }
}
