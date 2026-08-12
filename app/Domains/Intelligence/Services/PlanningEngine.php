<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\ExecutionPlanData;
use App\Domains\Intelligence\DTOs\ExecutionPlanStepData;
use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Models\AiTool;
use Illuminate\Support\Collection;

class PlanningEngine
{
    public function __construct(
        private readonly ToolRegistry $toolRegistry,
    ) {}

    public function plan(string $objective, ?Agent $agent = null, array $context = []): ExecutionPlanData
    {
        $availableTools = collect($this->toolRegistry->definitions());
        $requestedTools = $this->selectTools($objective, $agent, $availableTools);
        $dependencies = ['context', 'permissions', 'verification'];

        $steps = [
            new ExecutionPlanStepData(
                sequence: 1,
                title: 'Assemble context, permissions, and runtime constraints',
                toolSlug: null,
                stepKind: 'analysis',
                verificationRequirements: ['context_scored', 'permissions_loaded'],
                inputPayload: ['context_keys' => array_keys($context)],
                metadata: ['phase' => 'context'],
            ),
        ];

        foreach (array_values($requestedTools) as $index => $tool) {
            $steps[] = new ExecutionPlanStepData(
                sequence: $index + 2,
                title: "Execute {$tool}",
                toolSlug: (string) $tool,
                stepKind: 'tool_call',
                dependencies: ['step-1'],
                requiredTools: [(string) $tool],
                verificationRequirements: ['tool_executed', 'tool_output_recorded'],
                retryLimit: 2,
                inputPayload: ['objective' => $objective, 'selected_tool' => $tool],
                metadata: ['phase' => 'execution'],
            );
        }

        if ($this->requiresWorkflow($objective)) {
            $dependencies[] = 'workflow';
            $steps[] = new ExecutionPlanStepData(
                sequence: count($steps) + 1,
                title: 'Orchestrate workflow actions and checkpoints',
                stepKind: 'workflow',
                dependencies: ['step-1'],
                requiredTools: $requestedTools,
                verificationRequirements: ['workflow_started'],
                retryLimit: 1,
                inputPayload: ['objective' => $objective],
                metadata: ['phase' => 'workflow'],
            );
        }

        $steps[] = new ExecutionPlanStepData(
            sequence: count($steps) + 1,
            title: 'Verify completeness and respond',
            toolSlug: null,
            stepKind: 'verification',
            dependencies: ['step-1'],
            requiredTools: $requestedTools,
            verificationRequirements: ['confidence_above_threshold'],
            inputPayload: ['objective' => $objective],
            metadata: ['phase' => 'verification'],
        );

        return new ExecutionPlanData(
            objective: $objective,
            steps: $steps,
            requiredTools: $requestedTools,
            dependencies: $dependencies,
            estimatedComplexity: $this->estimateComplexity($objective, $requestedTools, $context),
            completionState: 'planned',
            maxIterations: (int) config('intelligence.agent_runtime.max_iterations', 5),
            retryStrategy: [
                'tool_failures' => 'retry_same_tool',
                'authorization_failures' => 'stop_and_report',
                'max_retries_per_step' => 2,
            ],
            metadata: [
                'agent' => $agent?->slug,
                'context_keys' => array_keys($context),
                'tool_count' => count($requestedTools),
            ],
        );
    }

    /**
     * @param Collection<int, AiTool> $availableTools
     * @return list<string>
     */
    private function selectTools(string $objective, ?Agent $agent, Collection $availableTools): array
    {
        $catalogued = $availableTools
            ->filter(fn (AiTool $tool): bool => ! $tool->deprecated && $tool->status->value === 'active')
            ->values();

        $allowed = $agent !== null && $agent->allowed_tools !== null
            ? $catalogued->whereIn('slug', $agent->allowed_tools)->values()
            : $catalogued;

        $matches = $allowed
            ->map(function (AiTool $tool) use ($objective): array {
                $haystack = strtolower($objective);
                $score = 0;

                foreach ([$tool->slug, $tool->name, $tool->category, ...($tool->tags ?? [])] as $keyword) {
                    if ($keyword !== null && str_contains($haystack, strtolower((string) $keyword))) {
                        $score += 20;
                    }
                }

                if ($tool->slug === 'current_datetime' && preg_match('/today|date|time|deadline|schedule/i', $objective) === 1) {
                    $score += 25;
                }

                if ($tool->slug === 'platform_status' && preg_match('/status|health|runtime|platform|diagnostic/i', $objective) === 1) {
                    $score += 25;
                }

                if ($tool->slug === 'conversation_summary' && preg_match('/summari[sz]e|recap|conversation/i', $objective) === 1) {
                    $score += 25;
                }

                if ($tool->slug === 'erp_lookup_stub' && preg_match('/project|member|program|beneficiar|meeting|finance|document|erp/i', $objective) === 1) {
                    $score += 20;
                }

                return ['slug' => $tool->slug, 'score' => $score];
            })
            ->filter(fn (array $match): bool => $match['score'] > 0)
            ->sortByDesc('score')
            ->pluck('slug')
            ->values()
            ->all();

        if ($matches === []) {
            $fallback = $allowed->pluck('slug')->values()->all();

            return array_slice($fallback === [] ? ['current_datetime'] : $fallback, 0, 3);
        }

        return array_slice($matches, 0, 3);
    }

    private function estimateComplexity(string $objective, array $tools, array $context): string
    {
        $signals = count($tools) + count($context);

        if ($this->requiresWorkflow($objective)) {
            $signals += 2;
        }

        return $signals >= 5 ? 'high' : ($signals >= 3 ? 'medium' : 'low');
    }

    private function requiresWorkflow(string $objective): bool
    {
        return preg_match('/create|assign|generate|notify|schedule|approve|workflow|orchestrate/i', $objective) === 1;
    }
}
