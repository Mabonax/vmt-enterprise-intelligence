<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Knowledge\DTOs\KnowledgeSearchQueryData;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeChunk;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEmbedding;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeSource;
use App\Domains\Intelligence\Knowledge\Services\EmbeddingService;
use App\Domains\Intelligence\Knowledge\Services\SemanticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemanticSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_semantic_search_returns_ranked_document_results(): void
    {
        $source = KnowledgeSource::query()->create([
            'source_type' => 'document',
            'source_id' => 'workflow-guide-source',
            'title' => 'Workflow Guide Source',
            'status' => 'active',
        ]);

        $document = KnowledgeDocument::query()->create([
            'knowledge_source_id' => $source->id,
            'title' => 'Customer Workflow Guide',
            'slug' => 'customer_workflow_guide',
            'source_type' => 'document',
            'mime_type' => 'text/plain',
            'status' => 'indexed',
            'visibility' => 'organization',
            'classification' => 'internal',
            'content' => 'Customer workflow guide for approvals and onboarding.',
            'summary' => 'Customer workflow guide',
            'quality_score' => 0.76,
            'version' => 1,
            'checksum' => sha1('guide'),
        ]);

        $chunk = KnowledgeChunk::query()->create([
            'knowledge_source_id' => $source->id,
            'knowledge_document_id' => $document->id,
            'position' => 1,
            'content' => 'Customer workflow guide for approvals and onboarding.',
            'chunk_strategy' => 'paragraph',
            'chunk_order' => 1,
            'token_count' => 7,
            'overlap' => 0,
            'checksum' => sha1('guide chunk'),
            'chunk_metadata' => [],
            'embedding_metadata' => [],
        ]);

        KnowledgeEmbedding::query()->create([
            'knowledge_chunk_id' => $chunk->id,
            'provider' => 'internal',
            'model' => 'hash-vector-v1',
            'status' => 'generated',
            'vector' => app(EmbeddingService::class)->generate($chunk->content)['vector'],
            'dimensions' => 16,
            'checksum' => sha1($chunk->content),
        ]);

        $results = app(SemanticSearchService::class)->search(new KnowledgeSearchQueryData(
            query: 'customer onboarding workflow',
            limit: 5,
        ));

        $this->assertNotEmpty($results);
        $this->assertSame('document', $results[0]->type);
    }
}
