<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Models\ExecutionTrace;
use App\Domains\Intelligence\Tools\Models\ToolExecution;

class ReplayEngine
{
    public function __construct(
        private readonly ToolExecutor $toolExecutor,
    ) {}

    public function replay(ToolExecution $execution, ToolContext $context, ?ExecutionTrace $trace = null): mixed
    {
        return $this->toolExecutor->execute(
            $execution->replay_payload['tool'],
            $execution->replay_payload['input'] ?? [],
            $context,
            $trace,
        );
    }
}
