<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentCostTracking extends AgentRecord
{
    protected $table = 'agent_cost_tracking';

    protected $fillable = ['agent_session_id', 'agent_id', 'input_tokens', 'output_tokens', 'estimated_cost', 'tool_costs', 'metadata'];

    protected function casts(): array
    {
        return ['estimated_cost' => 'float', 'tool_costs' => 'array', 'metadata' => 'array'];
    }
}
