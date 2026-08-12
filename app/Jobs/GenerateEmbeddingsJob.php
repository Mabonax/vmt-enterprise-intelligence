<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEmbedding;
use App\Domains\Intelligence\Knowledge\Services\EmbeddingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateEmbeddingsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $documentId,
    ) {}

    public function handle(EmbeddingService $embeddings): void
    {
        $document = KnowledgeDocument::query()->findOrFail($this->documentId);

        foreach ($document->chunks as $chunk) {
            $payload = $embeddings->generate($chunk->content);

            KnowledgeEmbedding::query()->updateOrCreate(
                ['knowledge_chunk_id' => $chunk->id],
                [
                    'provider' => $payload['provider'],
                    'model' => $payload['model'],
                    'status' => 'generated',
                    'vector' => $payload['vector'],
                    'dimensions' => $payload['dimensions'],
                    'checksum' => $payload['checksum'],
                    'metadata' => [],
                ],
            );
        }

        $document->update(['status' => 'embedded']);
    }
}
