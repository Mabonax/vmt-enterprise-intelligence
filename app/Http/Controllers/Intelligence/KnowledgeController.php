<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\Knowledge\DTOs\KnowledgeIngestionData;
use App\Domains\Intelligence\Knowledge\DTOs\KnowledgeSearchQueryData;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEmbedding;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeFeedback;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeLearningCycle;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeMemory;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeRelationship;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeAnalyticsService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeGraphService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeHealthService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeLifecycleService;
use App\Domains\Intelligence\Knowledge\Services\SemanticSearchService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Intelligence\KnowledgeIngestionRequest;
use App\Http\Requests\Intelligence\KnowledgeSearchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function __construct(
        private readonly KnowledgeLifecycleService $lifecycle,
        private readonly SemanticSearchService $search,
        private readonly KnowledgeGraphService $graph,
        private readonly KnowledgeAnalyticsService $analytics,
        private readonly KnowledgeHealthService $health,
    ) {}

    public function search(KnowledgeSearchRequest $request): JsonResponse
    {
        $organizationId = $request->user()?->organization_id;
        abort_unless(is_string($organizationId) && $organizationId !== '', 403, 'Organization context required.');

        $results = $this->search->search(new KnowledgeSearchQueryData(
            query: (string) $request->string('query'),
            filters: array_merge($request->input('filters', []), ['organization_id' => $organizationId]),
            limit: (int) $request->integer('limit', 8),
            workspace: (string) $request->string('workspace', 'intelligence'),
        ));

        return response()->json([
            'results' => array_map(fn ($result): array => [
                'type' => $result->type,
                'id' => $result->id,
                'title' => $result->title,
                'score' => $result->score,
                'payload' => $result->payload,
                'metadata' => $result->metadata,
            ], $results),
        ]);
    }

    public function upload(KnowledgeIngestionRequest $request): JsonResponse
    {
        $organizationId = $request->user()?->organization_id;
        abort_unless(is_string($organizationId) && $organizationId !== '', 403, 'Organization context required.');

        $document = $this->lifecycle->ingest(
            new KnowledgeIngestionData(
                title: (string) $request->string('title'),
                content: (string) $request->string('content'),
                sourceType: (string) $request->string('source_type'),
                mimeType: (string) $request->string('mime_type'),
                metadata: array_merge($request->input('metadata', []), [
                    'organization_id' => $organizationId,
                    'visibility' => 'organization',
                ]),
            ),
            (string) $request->string('workspace', 'intelligence'),
        );

        return response()->json([
            'document' => $document->only(['id', 'title', 'status', 'source_type']),
        ], 201);
    }

    public function graph(Request $request): JsonResponse
    {
        // Global graph and analytics tables have no enforceable organization scope yet.
        // Deny rather than expose another deployment's knowledge metadata.
        abort(403, 'Organization-scoped knowledge explorer is not yet available.');
    }

    public function memories(Request $request): JsonResponse
    {
        $organizationId = $request->user()?->organization_id;
        abort_unless(is_string($organizationId) && $organizationId !== '', 403);

        return response()->json([
            'memories' => KnowledgeMemory::query()
                ->where('tenant_id', $organizationId)
                ->where('visibility', 'organization')
                ->latest()->limit(25)->get(),
        ]);
    }

    public function relationships(Request $request): JsonResponse
    {
        // Global graph and analytics tables have no enforceable organization scope yet.
        // Deny rather than expose another deployment's knowledge metadata.
        abort(403, 'Organization-scoped knowledge explorer is not yet available.');
    }

    public function learningMetrics(Request $request): JsonResponse
    {
        // Global graph and analytics tables have no enforceable organization scope yet.
        // Deny rather than expose another deployment's knowledge metadata.
        abort(403, 'Organization-scoped knowledge explorer is not yet available.');
    }

    public function analytics(Request $request): JsonResponse
    {
        // Global graph and analytics tables have no enforceable organization scope yet.
        // Deny rather than expose another deployment's knowledge metadata.
        abort(403, 'Organization-scoped knowledge explorer is not yet available.');
    }

    public function embeddingStatus(Request $request): JsonResponse
    {
        // Global graph and analytics tables have no enforceable organization scope yet.
        // Deny rather than expose another deployment's knowledge metadata.
        abort(403, 'Organization-scoped knowledge explorer is not yet available.');
    }

    public function health(Request $request): JsonResponse
    {
        // Global graph and analytics tables have no enforceable organization scope yet.
        // Deny rather than expose another deployment's knowledge metadata.
        abort(403, 'Organization-scoped knowledge explorer is not yet available.');
    }
}
