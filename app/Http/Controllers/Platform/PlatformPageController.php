<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Support\Navigation\VipNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PlatformPageController extends Controller
{
    /**
     * Render a platform page by slug.
     */
    public function __invoke(Request $request): Response
    {
        $slug = (string) $request->route('page');
        $page = VipNavigation::find($slug);

        abort_if($page === null, 404);

        return Inertia::render('Platform/Show', [
            'page' => [
                'title' => $page['label'],
                'slug' => $page['slug'],
                'description' => $page['description'],
                'pillars' => $this->pillarsFor($page['slug']),
            ],
        ]);
    }

    /**
     * @return list<array{title: string, body: string}>
     */
    private function pillarsFor(string $slug): array
    {
        return match ($slug) {
            'organizations' => [
                ['title' => 'Tenant boundary', 'body' => 'Organization isolation will remain explicit, auditable, and separate from ERP ownership concerns.'],
                ['title' => 'ERP portfolio mapping', 'body' => 'Each organization can attach multiple ERP connections without coupling the intelligence core to one client system.'],
                ['title' => 'Gateway contracts', 'body' => 'Policies, provisioning workflows, and lifecycle events support secure ERP-to-gateway integration boundaries.'],
            ],
            'connections' => [
                ['title' => 'Secure exchange', 'body' => 'API keys, permissions, model allowances, and rate limits are scaffolded for future governance.'],
                ['title' => 'Protocol neutrality', 'body' => 'Connection models anticipate REST, webhook, and future MCP-style transport seams.'],
                ['title' => 'Auditability', 'body' => 'Request logging tables and domains are prepared before any live traffic exists.'],
            ],
            'gateway' => [
                ['title' => 'Single contract surface', 'body' => 'Chat, embeddings, OCR, speech, translation, streaming, and memory all share a provider-agnostic gateway contract seam.'],
                ['title' => 'Provider independence', 'body' => 'Application logic will target gateway contracts rather than concrete SDKs.'],
                ['title' => 'Operational controls', 'body' => 'Monitoring, retries, quotas, and failover remain separate implementation concerns for later phases.'],
            ],
            default => [
                ['title' => 'Gateway ready', 'body' => 'This workspace supports the Enterprise AI Gateway mission and is extended additively rather than rebuilt.'],
                ['title' => 'Contract first', 'body' => 'Configuration, routes, data shape, and storage seams keep ERP integrations stable while providers evolve.'],
                ['title' => 'Replaceable by design', 'body' => 'Each domain is structured so providers, repositories, and services can evolve independently.'],
            ],
        };
    }
}
