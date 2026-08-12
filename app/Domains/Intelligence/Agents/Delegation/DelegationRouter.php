<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Delegation;

use App\Domains\Intelligence\Agents\Enums\AgentRoleType;
use App\Domains\Intelligence\DTOs\ExecutionPlanStepData;

class DelegationRouter
{
    public function roleForStep(ExecutionPlanStepData $step): string
    {
        return match ($step->stepKind) {
            'workflow' => AgentRoleType::WorkflowAgent->value,
            'verification' => AgentRoleType::ReviewerAgent->value,
            'tool_call' => $this->toolRole((string) $step->toolSlug),
            default => AgentRoleType::PlanningAgent->value,
        };
    }

    private function toolRole(string $toolSlug): string
    {
        return match (true) {
            str_contains($toolSlug, 'document'), str_contains($toolSlug, 'knowledge') => AgentRoleType::KnowledgeAgent->value,
            str_contains($toolSlug, 'status'), str_contains($toolSlug, 'health') => AgentRoleType::ReportingAgent->value,
            default => AgentRoleType::ToolAgent->value,
        };
    }
}
