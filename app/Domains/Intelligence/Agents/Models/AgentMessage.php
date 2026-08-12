<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

class AgentMessage extends AgentRecord
{
    protected $fillable = ['agent_conversation_id', 'sender_agent_id', 'recipient_agent_id', 'message_type', 'status', 'subject', 'content', 'references', 'tool_output', 'metadata'];

    protected function casts(): array
    {
        return ['references' => 'array', 'tool_output' => 'array', 'metadata' => 'array'];
    }
}
