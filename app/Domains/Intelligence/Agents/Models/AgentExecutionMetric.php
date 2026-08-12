<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentExecutionMetric extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'agent_id', 'metric_key', 'metric_value', 'unit', 'metadata'];

    protected function casts(): array
    {
        return ['metric_value' => 'float', 'metadata' => 'array'];
    }
}
