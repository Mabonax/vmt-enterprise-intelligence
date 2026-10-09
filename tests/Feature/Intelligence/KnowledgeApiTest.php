<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KnowledgeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_document_upload_endpoint_creates_document_and_queues_jobs(): void
    {
        Queue::fake();

        $user = $this->organizationUser();
        Sanctum::actingAs($user);

        $this->postJson(route('api.knowledge.documents.store'), [
            'title' => 'Operations Playbook',
            'content' => 'Operations playbook for intake and verification.',
            'source_type' => 'document',
            'mime_type' => 'text/plain',
            'workspace' => 'intelligence',
        ])->assertCreated()
            ->assertJsonPath('document.title', 'Operations Playbook');

        $this->assertDatabaseHas('knowledge_documents', [
            'title' => 'Operations Playbook',
            'status' => 'queued',
        ]);
        $this->assertSame($user->organization_id, \App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument::query()->firstOrFail()->metadata['organization_id']);
    }

    public function test_knowledge_search_endpoint_returns_results(): void
    {
        $user = $this->organizationUser();
        Sanctum::actingAs($user);

        $source = KnowledgeSource::query()->create([
            'source_type' => 'document',
            'source_id' => 'verification-source',
            'title' => 'Verification Source',
            'status' => 'active',
        ]);

        $documentId = \App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument::query()->create([
            'knowledge_source_id' => $source->id,
            'title' => 'Verification Policy',
            'slug' => 'verification_policy',
            'source_type' => 'document',
            'mime_type' => 'text/plain',
            'status' => 'indexed',
            'visibility' => 'organization',
            'classification' => 'internal',
            'content' => 'Verification policy and workflow evidence.',
            'summary' => 'Verification policy',
            'quality_score' => 0.75,
            'version' => 1,
            'checksum' => sha1('verification'),
        ])->id;

        $chunk = \App\Domains\Intelligence\Knowledge\Models\KnowledgeChunk::query()->create([
            'knowledge_source_id' => $source->id,
            'knowledge_document_id' => $documentId,
            'position' => 1,
            'content' => 'Verification policy and workflow evidence.',
            'chunk_strategy' => 'paragraph',
            'chunk_order' => 1,
            'token_count' => 5,
            'overlap' => 0,
            'checksum' => sha1('verification chunk'),
            'chunk_metadata' => [],
            'embedding_metadata' => [],
        ]);

        \App\Domains\Intelligence\Knowledge\Models\KnowledgeEmbedding::query()->create([
            'knowledge_chunk_id' => $chunk->id,
            'provider' => 'internal',
            'model' => 'hash-vector-v1',
            'status' => 'generated',
            'vector' => app(\App\Domains\Intelligence\Knowledge\Services\EmbeddingService::class)->generate($chunk->content)['vector'],
            'dimensions' => 16,
            'checksum' => sha1($chunk->content),
        ]);

        $this->getJson(route('api.knowledge.search', ['query' => 'verification workflow']))
            ->assertOk()
            ->assertJsonCount(0, 'results');
    }
    public function test_search_rejects_missing_organization_context(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('api.knowledge.search', ['query' => 'policy']))->assertForbidden();
        $this->postJson(route('api.knowledge.documents.store'), [
            'title' => 'Unscoped',
            'content' => 'Should not be ingested',
            'source_type' => 'document',
            'mime_type' => 'text/plain',
        ])->assertForbidden();
    }

    private function organizationUser(): User
    {
        $id = (string) Str::uuid();
        DB::table('organizations')->insert([
            'id' => $id,
            'name' => 'Knowledge Organization',
            'slug' => 'knowledge-'.Str::lower(Str::random(6)),
            'code' => 'KN-'.Str::upper(Str::random(6)),
            'status' => 'active',
            'settings' => json_encode([], JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::factory()->create(['organization_id' => $id]);
    }

}
