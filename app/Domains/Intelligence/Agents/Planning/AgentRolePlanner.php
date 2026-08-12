<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Planning;

use App\Domains\Intelligence\Agents\Enums\AgentRoleType;

class AgentRolePlanner
{
    /**
     * @return list<string>
     */
    public function buildTeam(string $objective): array
    {
        $roles = [
            AgentRoleType::PlanningAgent->value,
            AgentRoleType::ResearchAgent->value,
            AgentRoleType::KnowledgeAgent->value,
            AgentRoleType::DeveloperAgent->value,
            AgentRoleType::ReviewerAgent->value,
            AgentRoleType::ExecutiveAdvisor->value,
        ];

        if (preg_match('/security|compliance|risk|privacy/i', $objective) === 1) {
            $roles[] = AgentRoleType::SecurityAgent->value;
            $roles[] = AgentRoleType::ComplianceAgent->value;
        }

        if (preg_match('/report|metric|dashboard|monitor/i', $objective) === 1) {
            $roles[] = AgentRoleType::ReportingAgent->value;
            $roles[] = AgentRoleType::DataAnalyst->value;
        }

        return array_values(array_unique($roles));
    }
}
