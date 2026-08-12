<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeScoringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class KnowledgeVerificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ?string $documentId = null,
    ) {}

    public function handle(KnowledgeScoringService $scoring): void
    {
        $query = KnowledgeDocument::query();

        if ($this->documentId !== null) {
            $query->where('id', $this->documentId);
        }

        foreach ($query->get() as $document) {
            $document->update([
                'quality_score' => $scoring->quality([
                    'freshness' => 0.80,
                    'authority' => 0.75,
                    'confidence' => 0.78,
                    'completeness' => 0.74,
                    'citation_quality' => 0.71,
                ]),
            ]);
        }
    }
}
