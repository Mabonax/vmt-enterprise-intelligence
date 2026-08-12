<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Resources;

use App\Domains\Intelligence\Models\Agent;

class EnterpriseAgentResource
{
    public static function make(Agent $agent): array
    {
        return [
            'id' => $agent->id,
            'name' => $agent->name,
            'slug' => $agent->slug,
            'agent_role_key' => $agent->agent_role_key,
            'status' => $agent->status->value,
            'visibility' => $agent->visibility->value,
            'default_provider' => $agent->default_provider,
            'default_model' => $agent->default_model,
            'reasoning_style' => $agent->reasoning_style,
            'risk_tolerance' => $agent->risk_tolerance,
            'verification_strategy' => $agent->verification_strategy,
            'memory_scope' => $agent->memory_scope,
            'delegation_enabled' => $agent->delegation_enabled,
            'approval_required' => $agent->approval_required,
        ];
    }
}
