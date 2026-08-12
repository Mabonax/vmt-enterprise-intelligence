<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReindexKnowledgeJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        KnowledgeEvent::query()->create([
            'event_type' => 'knowledge.reindexed',
            'subject_type' => 'knowledge_document',
            'subject_id' => 'all',
            'payload' => ['document_count' => KnowledgeDocument::query()->count()],
            'metadata' => [],
        ]);
    }
}
