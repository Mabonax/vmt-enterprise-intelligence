<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Knowledge\DTOs\KnowledgeSearchQueryData;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Services\SemanticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_search_only_retrieves_its_visible_documents(): void
    {
        foreach ([
            ['organization' => 'org-a', 'visibility' => 'organization', 'title' => 'Alpha clinic policy'],
            ['organization' => 'org-b', 'visibility' => 'organization', 'title' => 'Beta clinic policy'],
            ['organization' => 'org-a', 'visibility' => 'private', 'title' => 'Secret clinic policy'],
        ] as $record) {
            KnowledgeDocument::query()->create([
                'title' => $record['title'],
                'slug' => strtolower(str_replace(' ', '-', $record['title'])),
                'source_type' => 'document',
                'mime_type' => 'text/plain',
                'status' => 'indexed',
                'visibility' => $record['visibility'],
                'classification' => 'internal',
                'content' => $record['title'],
                'summary' => $record['title'],
                'version' => 1,
                'checksum' => sha1($record['title']),
                'metadata' => ['organization_id' => $record['organization']],
            ]);
        }

        $service = app(SemanticSearchService::class);
        $results = $service->search(new KnowledgeSearchQueryData(
            query: 'clinic policy',
            filters: ['organization_id' => 'org-a'],
        ));

        $this->assertCount(1, $results);
        $this->assertSame('Alpha clinic policy', $results[0]->title);

        $this->assertSame([], $service->search(new KnowledgeSearchQueryData(
            query: 'clinic policy'
        )));
    }
}
