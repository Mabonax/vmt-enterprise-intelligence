<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Communication;

use App\Domains\Intelligence\Agents\Models\AgentConversation;
use App\Domains\Intelligence\Agents\Models\AgentMessage;

class AgentMessenger
{
    public function open(string $sessionId, string $topic, array $participants = []): AgentConversation
    {
        return AgentConversation::query()->create([
            'agent_session_id' => $sessionId,
            'topic' => $topic,
            'status' => 'active',
            'participants' => $participants,
        ]);
    }

    public function send(
        AgentConversation $conversation,
        ?string $senderAgentId,
        ?string $recipientAgentId,
        string $type,
        string $content,
        ?string $subject = null,
        array $references = [],
        array $toolOutput = [],
        array $metadata = [],
    ): AgentMessage {
        return AgentMessage::query()->create([
            'agent_conversation_id' => $conversation->id,
            'sender_agent_id' => $senderAgentId,
            'recipient_agent_id' => $recipientAgentId,
            'message_type' => $type,
            'status' => 'sent',
            'subject' => $subject,
            'content' => $content,
            'references' => $references,
            'tool_output' => $toolOutput,
            'metadata' => $metadata,
        ]);
    }
}
