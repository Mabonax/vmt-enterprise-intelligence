<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\DTOs\PromptContext;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeRetrievalService;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ContextAssembler
{
    public function __construct(
        private readonly KnowledgeRetrievalService $knowledgeRetrieval,
    ) {}

    public function assemble(
        string $systemPrompt,
        array $conversationHistory = [],
        array $manualContext = [],
        array $pinnedContext = [],
        array $toolDefinitions = [],
        ?string $userPrompt = null,
        ?User $user = null,
    ): PromptContext {
        $runtimeContext = $this->buildRuntimeContext(
            conversationHistory: $conversationHistory,
            manualContext: $manualContext,
            pinnedContext: $pinnedContext,
            toolDefinitions: $toolDefinitions,
            userPrompt: $userPrompt,
            user: $user,
        );

        return new PromptContext(
            systemPrompt: $systemPrompt,
            conversationHistory: $conversationHistory,
            manualContext: $manualContext,
            pinnedContext: $pinnedContext,
            runtimeContext: $runtimeContext,
            toolDefinitions: $toolDefinitions,
            userPrompt: $userPrompt,
        );
    }

    /**
     * @param list<ChatMessage> $conversationHistory
     * @param list<array{name: string, description: string, schema: array<string, mixed>}> $toolDefinitions
     * @return array<string, mixed>
     */
    private function buildRuntimeContext(
        array $conversationHistory,
        array $manualContext,
        array $pinnedContext,
        array $toolDefinitions,
        ?string $userPrompt,
        ?User $user,
    ): array {
        $entries = [
            [
                'key' => 'request',
                'score' => $userPrompt === null ? 0 : 100,
                'payload' => ['prompt' => $userPrompt],
            ],
            [
                'key' => 'manual',
                'score' => $manualContext === [] ? 0 : 85,
                'payload' => $manualContext,
            ],
            [
                'key' => 'pinned',
                'score' => $pinnedContext === [] ? 0 : 90,
                'payload' => $pinnedContext,
            ],
            [
                'key' => 'history',
                'score' => $conversationHistory === [] ? 0 : 70,
                'payload' => [
                    'count' => count($conversationHistory),
                    'recent_messages' => collect($conversationHistory)
                        ->take(-3)
                        ->map(fn (ChatMessage $message): array => [
                            'role' => $message->role->value,
                            'content' => $message->content,
                        ])
                        ->values()
                        ->all(),
                ],
            ],
            [
                'key' => 'user_scope',
                'score' => $user === null ? 0 : 95,
                'payload' => $user === null ? [] : [
                    'user_id' => $user->id,
                    'organization_id' => $user->organization_id,
                    'permissions' => method_exists($user, 'getAllPermissions')
                        ? $user->getAllPermissions()->pluck('name')->values()->all()
                        : [],
                ],
            ],
            [
                'key' => 'tools',
                'score' => $toolDefinitions === [] ? 0 : 80,
                'payload' => [
                    'count' => count($toolDefinitions),
                    'available' => array_map(
                        static fn (array $tool): array => [
                            'name' => $tool['name'],
                            'description' => $tool['description'],
                        ],
                        array_slice($toolDefinitions, 0, 6),
                    ),
                ],
            ],
        ];

        if (
            config('intelligence.knowledge.enabled')
            && config('intelligence.knowledge.retrieval.enabled')
            && $userPrompt !== null
            && Schema::hasTable('knowledge_documents')
            && Schema::hasTable('knowledge_memories')
        ) {
            $knowledge = $this->knowledgeRetrieval->retrieveForPrompt(
                prompt: $userPrompt,
                filters: [
                    'organization_id' => $user?->organization_id,
                    'workspace' => 'intelligence',
                ],
                limit: (int) config('intelligence.knowledge.retrieval.default_limit', 5),
            );

            $entries[] = [
                'key' => 'knowledge',
                'score' => $knowledge['results'] === [] ? 0 : 88,
                'payload' => $knowledge,
            ];
        }

        $selected = collect($entries)
            ->filter(fn (array $entry): bool => $entry['score'] > 0)
            ->sortByDesc('score')
            ->values();

        return [
            'selected' => $selected->pluck('key')->all(),
            'scores' => $selected->mapWithKeys(fn (array $entry): array => [$entry['key'] => $entry['score']])->all(),
            'payloads' => $selected->mapWithKeys(fn (array $entry): array => [$entry['key'] => $entry['payload']])->all(),
        ];
    }
}
