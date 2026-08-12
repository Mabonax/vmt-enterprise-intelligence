<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

use App\Domains\Intelligence\Knowledge\DTOs\KnowledgeIngestionData;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeCollection;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeSource;
use App\Domains\Intelligence\Knowledge\Repositories\KnowledgeDocumentRepository;
use App\Jobs\ChunkDocumentJob;
use App\Jobs\DiscoverRelationshipsJob;
use App\Jobs\ExtractEntitiesJob;
use App\Jobs\GenerateEmbeddingsJob;

class KnowledgeLifecycleService
{
    public function __construct(
        private readonly KnowledgeDocumentRepository $documents,
    ) {}

    public function ingest(KnowledgeIngestionData $data, string $workspace = 'intelligence'): KnowledgeDocument
    {
        $collection = KnowledgeCollection::query()->firstOrCreate(
            ['slug' => $workspace],
            ['name' => ucfirst($workspace), 'workspace' => $workspace, 'description' => ucfirst($workspace).' knowledge collection'],
        );

        $source = KnowledgeSource::query()->create([
            'source_type' => $data->sourceType,
            'source_id' => (string) ($data->metadata['source_id'] ?? str()->uuid()),
            'title' => $data->title,
            'uri' => $data->metadata['uri'] ?? null,
            'status' => 'ingesting',
            'metadata' => $data->metadata,
        ]);

        $document = $this->documents->create([
            'knowledge_collection_id' => $collection->id,
            'knowledge_source_id' => $source->id,
            'title' => $data->title,
            'slug' => str($data->title)->slug('_')->toString(),
            'source_type' => $data->sourceType,
            'mime_type' => $data->mimeType,
            'language' => 'en',
            'status' => 'queued',
            'visibility' => $data->metadata['visibility'] ?? 'organization',
            'classification' => $data->metadata['classification'] ?? 'internal',
            'content' => $data->content,
            'summary' => str($data->content)->limit(240)->toString(),
            'keywords' => [],
            'quality_score' => 0,
            'version' => 1,
            'checksum' => sha1($data->content),
            'metadata' => $data->metadata,
        ]);

        ChunkDocumentJob::dispatch($document->id);
        GenerateEmbeddingsJob::dispatch($document->id);
        ExtractEntitiesJob::dispatch($document->id);
        DiscoverRelationshipsJob::dispatch($document->id);

        return $document;
    }
}
