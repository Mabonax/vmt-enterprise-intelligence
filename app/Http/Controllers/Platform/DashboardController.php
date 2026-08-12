<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    /**
     * Render the executive platform dashboard.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'hero' => [
                'eyebrow' => 'Enterprise intelligence runtime',
                'title' => 'VIP command center foundation',
                'description' => 'Phase 2 extends the platform with a provider-neutral runtime for conversations, token control, tools, agents, exports, and usage tracking without introducing provider SDK logic.',
            ],
            'widgets' => [
                [
                    'title' => 'Intelligence workspace',
                    'description' => 'Conversation runtime, prompt assembly, token control, and usage tracking are now scaffolded behind one bounded context.',
                ],
                [
                    'title' => 'Provider registry',
                    'description' => 'Discovered provider stubs expose a shared contract for chat, streaming, embeddings, model catalogs, and token estimation.',
                ],
                [
                    'title' => 'Conversation persistence',
                    'description' => 'Conversation, message, attachment, export, tool execution, and usage tables are additive and UUID-based.',
                ],
                [
                    'title' => 'Operational posture',
                    'description' => 'Streaming, tools, agents, and provider integrations remain stubbed and ready for future phases.',
                ],
            ],
        ]);
    }
}
