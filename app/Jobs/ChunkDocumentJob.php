<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeChunk;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Services\DocumentChunker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ChunkDocumentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $documentId,
    ) {}

    public function handle(DocumentChunker $chunker): void
    {
        $document = KnowledgeDocument::query()->findOrFail($this->documentId);
        $chunks = $chunker->chunk(
            $document->content,
            (string) ($document->metadata['chunk_strategy'] ?? config('intelligence.knowledge.chunking.default_strategy', 'paragraph')),
        );

        KnowledgeChunk::query()->where('knowledge_document_id', $document->id)->delete();

        foreach ($chunks as $chunk) {
            KnowledgeChunk::query()->create([
                'knowledge_source_id' => $document->knowledge_source_id,
                'knowledge_document_id' => $document->id,
                'position' => $chunk['order'],
                'content' => $chunk['content'],
                'chunk_strategy' => $chunk['strategy'],
                'chunk_order' => $chunk['order'],
                'token_count' => $chunk['token_count'],
                'overlap' => $chunk['overlap'],
                'checksum' => $chunk['checksum'],
                'chunk_metadata' => [],
                'embedding_metadata' => [],
                'metadata' => [],
            ]);
        }

        $document->update(['status' => 'chunked']);
    }
}
