<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Resources;

use App\Domains\Intelligence\Agents\Models\AgentSession;

class AgentSessionResource
{
    public static function make(AgentSession $session): array
    {
        return [
            'id' => $session->id,
            'session_key' => $session->session_key,
            'title' => $session->title,
            'objective' => $session->objective,
            'status' => $session->status,
            'execution_mode' => $session->execution_mode,
            'approval_role' => $session->approval_role,
            'requires_human_approval' => $session->requires_human_approval,
            'started_at' => $session->started_at?->toIso8601String(),
            'completed_at' => $session->completed_at?->toIso8601String(),
            'context_payload' => $session->context_payload,
            'result_payload' => $session->result_payload,
            'metadata' => $session->metadata,
        ];
    }
}
