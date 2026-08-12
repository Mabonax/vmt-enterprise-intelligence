<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Agents\Models\AgentSession;
use App\Domains\Intelligence\Agents\Services\MultiAgentCoordinator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExecuteMultiAgentSessionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $agentSessionId,
    ) {}

    public function handle(MultiAgentCoordinator $coordinator): void
    {
        $session = AgentSession::query()->findOrFail($this->agentSessionId);

        if (! in_array($session->status, ['queued', 'running'], true)) {
            return;
        }

        $coordinator->runSession($session);
    }
}
