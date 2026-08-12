<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEntity;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExtractEntitiesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $documentId,
    ) {}

    public function handle(KnowledgeExtractor $extractor): void
    {
        $document = KnowledgeDocument::query()->findOrFail($this->documentId);
        $entities = $extractor->extract($document->content);

        foreach ($entities as $entity) {
            KnowledgeEntity::query()->updateOrCreate(
                [
                    'knowledge_document_id' => $document->id,
                    'normalized_name' => str((string) $entity['name'])->lower()->toString(),
                ],
                [
                    'entity_type' => $entity['type'],
                    'name' => $entity['name'],
                    'confidence_score' => $entity['confidence_score'],
                    'occurrences' => 1,
                    'metadata' => [],
                ],
            );
        }

        $document->update(['status' => 'entities_extracted']);
    }
}
