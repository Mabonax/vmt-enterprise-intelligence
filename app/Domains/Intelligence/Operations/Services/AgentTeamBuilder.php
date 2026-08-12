<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\AgentCatalog;
use App\Domains\Intelligence\Operations\Models\EnterpriseMission;

class AgentTeamBuilder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildForMission(EnterpriseMission $mission, array $requiredSkills = []): array
    {
        $catalog = AgentCatalog::query()
            ->with(['skills', 'capabilities', 'availability'])
            ->where('status', 'active')
            ->get();

        return $catalog
            ->map(function (AgentCatalog $agent) use ($requiredSkills): array {
                $skillMatches = $agent->skills
                    ->filter(fn ($skill) => in_array($skill->skill_key, $requiredSkills, true))
                    ->count();

                $capacity = (float) optional($agent->availability->first())->capacity_percentage ?: 100.0;
                $score = ((float) $agent->historical_success_rate * 0.4)
                    + ((float) $agent->trust_score * 0.3)
                    + ($skillMatches * 10)
                    + (($capacity / 100) * 20)
                    - ((float) $agent->current_workload * 0.2);

                return [
                    'agent_id' => $agent->agent_id,
                    'catalog_id' => $agent->id,
                    'name' => $agent->name,
                    'provider' => $agent->provider,
                    'preferred_model' => $agent->preferred_model,
                    'score' => round($score, 2),
                ];
            })
            ->sortByDesc('score')
            ->take(4)
            ->values()
            ->all();
    }
}
