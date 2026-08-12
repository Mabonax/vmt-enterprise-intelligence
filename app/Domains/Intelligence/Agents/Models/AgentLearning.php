<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentLearning extends AgentRecord
{
    protected $table = 'agent_learning';

    protected $fillable = ['agent_session_id', 'learning_type', 'summary', 'best_practices', 'workflow_templates', 'decision_templates', 'metadata'];

    protected function casts(): array
    {
        return ['best_practices' => 'array', 'workflow_templates' => 'array', 'decision_templates' => 'array', 'metadata' => 'array'];
    }
}
