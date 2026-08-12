<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Models\ExecutionTrace;
use App\Domains\Intelligence\Tools\DTOs\ToolExecutionData;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use App\Domains\Intelligence\Tools\Models\ExecutionStream;
use App\Domains\Intelligence\Tools\Models\ToolCost;
use App\Domains\Intelligence\Tools\Models\ToolExecution;
use App\Domains\Intelligence\Tools\Models\ToolExecutionGraph;
use App\Domains\Intelligence\Tools\Models\ToolUsage;

class ToolUsageTracker
{
    public function record(EnterpriseTool $tool, ToolExecutionData $execution, ToolContext $context, ?ExecutionTrace $trace = null): ToolExecution
    {
        $toolExecution = ToolExecution::query()->create([
            'enterprise_tool_id' => $tool->id,
            'execution_trace_id' => $trace?->id,
            'status' => $execution->success ? 'completed' : 'failed',
            'version' => $tool->version,
            'connector_type' => $tool->connector_type,
            'input_payload' => $execution->input,
            'output_payload' => $execution->output,
            'replay_payload' => [
                'tool' => $tool->slug,
                'version' => $tool->version,
                'input' => $execution->input,
                'permissions' => $execution->metadata['authorization'] ?? [],
            ],
            'error_message' => $execution->error,
            'duration_ms' => $execution->durationMs,
            'metadata' => $execution->metadata,
        ]);

        ToolExecutionGraph::query()->create([
            'execution_trace_id' => $trace?->id,
            'tool_execution_id' => $toolExecution->id,
            'node_key' => $tool->slug.':'.$toolExecution->id,
            'depth' => 0,
            'metadata' => ['version' => $tool->version],
        ]);

        ToolUsage::query()->create([
            'enterprise_tool_id' => $tool->id,
            'user_id' => $context->user?->id,
            'execution_trace_id' => $trace?->id,
            'success' => $execution->success,
            'duration_ms' => $execution->durationMs,
            'tokens' => (int) ($execution->metadata['tokens'] ?? 0),
            'bandwidth_bytes' => (int) ($execution->metadata['bandwidth_bytes'] ?? 0),
            'storage_bytes' => (int) ($execution->metadata['storage_bytes'] ?? 0),
            'metadata' => $execution->metadata,
        ]);

        ToolCost::query()->create([
            'enterprise_tool_id' => $tool->id,
            'execution_trace_id' => $trace?->id,
            'api_usage_cost' => (float) ($execution->metadata['api_usage_cost'] ?? 0),
            'llm_token_cost' => (float) ($execution->metadata['llm_token_cost'] ?? 0),
            'storage_cost' => (float) ($execution->metadata['storage_cost'] ?? 0),
            'bandwidth_cost' => (float) ($execution->metadata['bandwidth_cost'] ?? 0),
            'estimated_cost' => (float) ($execution->metadata['estimated_cost'] ?? 0),
            'credits_consumed' => (float) ($execution->metadata['credits_consumed'] ?? 0),
            'metadata' => $execution->metadata,
        ]);

        foreach (($execution->metadata['stream'] ?? ['Completed']) as $index => $message) {
            ExecutionStream::query()->create([
                'tool_execution_id' => $toolExecution->id,
                'status' => $execution->success ? 'completed' : 'failed',
                'message' => (string) $message,
                'sequence' => $index + 1,
                'metadata' => [],
            ]);
        }

        return $toolExecution;
    }
}
