<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeChunk;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEmbedding;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeMemory;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeSource;
use App\Domains\Intelligence\Services\ContextAssembler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeRetrievalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_context_assembler_includes_knowledge_retrieval_payloads_when_available(): void
    {
        $source = KnowledgeSource::query()->create([
            'source_type' => 'document',
            'source_id' => 'policy-source',
            'title' => 'Policy source',
            'status' => 'active',
        ]);

        $document = KnowledgeDocument::query()->create([
            'knowledge_source_id' => $source->id,
            'title' => 'Policy Handbook',
            'slug' => 'policy_handbook',
            'source_type' => 'document',
            'mime_type' => 'text/plain',
            'status' => 'indexed',
            'visibility' => 'organization',
            'classification' => 'internal',
            'content' => 'Patient onboarding policy and workflow approvals',
            'summary' => 'Patient onboarding policy',
            'quality_score' => 0.82,
            'version' => 1,
            'checksum' => sha1('policy'),
        ]);

        $chunk = KnowledgeChunk::query()->create([
            'knowledge_source_id' => $source->id,
            'knowledge_document_id' => $document->id,
            'position' => 1,
            'content' => 'Patient onboarding policy and workflow approvals',
            'chunk_strategy' => 'paragraph',
            'chunk_order' => 1,
            'token_count' => 6,
            'overlap' => 0,
            'checksum' => sha1('policy chunk'),
            'chunk_metadata' => [],
            'embedding_metadata' => [],
        ]);

        KnowledgeEmbedding::query()->create([
            'knowledge_chunk_id' => $chunk->id,
            'provider' => 'internal',
            'model' => 'hash-vector-v1',
            'status' => 'generated',
            'vector' => app(\App\Domains\Intelligence\Knowledge\Services\EmbeddingService::class)->generate($chunk->content)['vector'],
            'dimensions' => 16,
            'checksum' => sha1($chunk->content),
        ]);

        KnowledgeMemory::query()->create([
            'memory_type' => 'policy',
            'title' => 'Onboarding decision',
            'summary' => 'Use the onboarding policy for approvals',
            'content' => 'Use the onboarding policy for approvals and workflow routing.',
            'visibility' => 'organization',
            'classification' => 'internal',
            'importance' => 0.80,
            'confidence' => 0.84,
            'embedding' => app(\App\Domains\Intelligence\Knowledge\Services\EmbeddingService::class)->generate('Use the onboarding policy for approvals and workflow routing.')['vector'],
            'usage_count' => 0,
        ]);

        $context = app(ContextAssembler::class)->assemble(
            systemPrompt: 'Runtime',
            userPrompt: 'Find the onboarding policy workflow',
        );

        $this->assertContains('knowledge', $context->runtimeContext['selected']);
        $this->assertNotEmpty($context->runtimeContext['payloads']['knowledge']['results']);
    }
}
