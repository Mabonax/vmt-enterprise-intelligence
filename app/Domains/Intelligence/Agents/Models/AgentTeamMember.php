<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentTeamMember extends AgentRecord
{
    protected $fillable = ['agent_session_id', 'agent_id', 'agent_role_id', 'member_name', 'member_type', 'status', 'order_column', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
