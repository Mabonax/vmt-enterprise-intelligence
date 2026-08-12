<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Services;

use App\Domains\Intelligence\Agents\Enums\AgentRoleType;
use App\Domains\Intelligence\Agents\Models\AgentCapability;
use App\Domains\Intelligence\Agents\Models\AgentRole;

class AgentRoleCatalog
{
    public function sync(): void
    {
        foreach ($this->definitions() as $definition) {
            $role = AgentRole::query()->updateOrCreate(
                ['role_key' => $definition['role_key']],
                $definition,
            );

            AgentCapability::query()->where('agent_role_id', $role->id)->delete();

            foreach ($definition['capabilities'] as $capability) {
                AgentCapability::query()->create([
                    'agent_role_id' => $role->id,
                    'capability_key' => $capability,
                    'capability_type' => 'role',
                    'confidence_weight' => 0.80,
                ]);
            }
        }
    }

    public function find(string $roleKey): ?AgentRole
    {
        return AgentRole::query()->where('role_key', $roleKey)->first();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function definitions(): array
    {
        return [
            $this->definition(AgentRoleType::ResearchAgent, 'Research Agent', 'evidence-first', 'low', ['research', 'evidence', 'discovery'], ['document_lookup_stub', 'erp_lookup_stub']),
            $this->definition(AgentRoleType::ArchitectAgent, 'Architect Agent', 'systems', 'moderate', ['architecture', 'design', 'tradeoffs'], ['platform_status']),
            $this->definition(AgentRoleType::PlanningAgent, 'Planning Agent', 'sequential', 'low', ['planning', 'branching', 'roadmaps'], ['current_datetime']),
            $this->definition(AgentRoleType::DeveloperAgent, 'Developer Agent', 'delivery', 'moderate', ['implementation', 'integration', 'execution'], ['erp_lookup_stub']),
            $this->definition(AgentRoleType::ReviewerAgent, 'Reviewer Agent', 'skeptical', 'low', ['review', 'quality', 'verification'], ['conversation_summary']),
            $this->definition(AgentRoleType::QaAgent, 'QA Agent', 'test-driven', 'low', ['qa', 'testing', 'regression'], ['platform_status']),
            $this->definition(AgentRoleType::SecurityAgent, 'Security Agent', 'risk-led', 'low', ['security', 'compliance', 'authorization'], ['platform_status']),
            $this->definition(AgentRoleType::KnowledgeAgent, 'Knowledge Agent', 'retrieval-first', 'low', ['knowledge', 'memory', 'graph'], ['document_lookup_stub', 'conversation_summary']),
            $this->definition(AgentRoleType::DocumentationAgent, 'Documentation Agent', 'structured', 'low', ['documentation', 'records', 'guides'], ['conversation_summary']),
            $this->definition(AgentRoleType::ReportingAgent, 'Reporting Agent', 'summarize', 'low', ['reporting', 'metrics', 'executive output'], ['platform_status', 'conversation_summary']),
            $this->definition(AgentRoleType::DataAnalyst, 'Data Analyst', 'analytical', 'low', ['analysis', 'insights', 'trend detection'], ['calculator']),
            $this->definition(AgentRoleType::WorkflowAgent, 'Workflow Agent', 'orchestration', 'moderate', ['workflow', 'approvals', 'routing'], ['current_datetime']),
            $this->definition(AgentRoleType::ToolAgent, 'Tool Agent', 'execution', 'moderate', ['tools', 'connectors', 'runtime execution'], ['platform_status', 'current_datetime']),
            $this->definition(AgentRoleType::CommunicationAgent, 'Communication Agent', 'handover', 'low', ['communication', 'handover', 'coordination'], ['conversation_summary']),
            $this->definition(AgentRoleType::ComplianceAgent, 'Compliance Agent', 'policy', 'low', ['policy', 'compliance', 'auditability'], ['platform_status']),
            $this->definition(AgentRoleType::ExecutiveAdvisor, 'Executive Advisor', 'synthesis', 'low', ['strategy', 'decisions', 'summary'], ['conversation_summary']),
        ];
    }

    private function definition(AgentRoleType $type, string $name, string $reasoningStyle, string $riskTolerance, array $capabilities, array $preferredTools): array
    {
        return [
            'role_key' => $type->value,
            'name' => $name,
            'description' => "{$name} specialization for enterprise multi-agent execution.",
            'default_prompt' => "You are the {$name}. Operate inside the VMT enterprise intelligence runtime with explicit evidence, tool discipline, and additive execution.",
            'capabilities' => $capabilities,
            'preferred_tools' => $preferredTools,
            'reasoning_style' => $reasoningStyle,
            'risk_tolerance' => $riskTolerance,
            'verification_strategy' => 'reviewer',
            'memory_scope' => 'shared',
            'metadata' => ['builtin' => true],
        ];
    }
}
