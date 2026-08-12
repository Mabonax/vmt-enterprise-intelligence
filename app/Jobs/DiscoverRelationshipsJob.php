<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeRelationship;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeGraphService;
use App\Domains\Intelligence\Knowledge\Services\RelationshipEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DiscoverRelationshipsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $documentId,
    ) {}

    public function handle(RelationshipEngine $engine, KnowledgeGraphService $graph): void
    {
        $document = KnowledgeDocument::query()->findOrFail($this->documentId);
        $entities = $document->chunks()->count() > 0
            ? $document->chunks()->take(5)->get()->map(fn ($chunk) => ['name' => str($chunk->content)->limit(30)->toString(), 'confidence_score' => 0.70])->all()
            : [];
        $relationships = $engine->discover($entities);
        $documentNode = $graph->upsertNode('document', $document->id, $document->title);

        foreach ($relationships as $relationship) {
            KnowledgeRelationship::query()->create([
                'source_type' => 'document',
                'source_id' => $document->id,
                'target_type' => 'entity',
                'target_id' => sha1($relationship['target_name']),
                'relationship' => $relationship['relationship'],
                'confidence_score' => $relationship['confidence_score'],
                'metadata' => $relationship,
            ]);

            $entityNode = $graph->upsertNode('entity', sha1($relationship['target_name']), $relationship['target_name']);
            $graph->link($documentNode, $entityNode, $relationship['relationship'], $relationship['confidence_score']);
        }

        $document->update(['status' => 'relationships_discovered']);
    }
}
