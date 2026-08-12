<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\DTOs\ToolExecutionResult;
use App\Domains\Intelligence\DTOs\ToolResult;
use App\Domains\Intelligence\Exceptions\ToolExecutionException;
use App\Domains\Intelligence\Models\AiTool;
use App\Domains\Intelligence\Models\ExecutionTrace;
use App\Domains\Intelligence\Models\ToolExecutionLog;
use App\Domains\Intelligence\Tools\DTOs\ToolExecutionData;
use App\Domains\Intelligence\Tools\Sandbox\ToolExecutionSandbox;

class ToolExecutor
{
    public function __construct(
        private readonly ToolResolver $resolver,
        private readonly ToolApprovalService $approvalService,
        private readonly ToolRegistry $toolRegistry,
        private readonly ConnectorManager $connectorManager,
        private readonly ToolAuthorizationService $toolAuthorization,
        private readonly ToolUsageTracker $usageTracker,
        private readonly ToolHealthMonitor $healthMonitor,
        private readonly ToolExecutionSandbox $sandbox,
    ) {}

    public function execute(string $slug, array $payload, ToolContext $context, ?ExecutionTrace $trace = null): ToolExecutionResult
    {
        $enterpriseTool = $this->toolRegistry->findEnterpriseTool($slug);

        if ($enterpriseTool !== null) {
            return $this->executeEnterpriseTool($enterpriseTool, $payload, $context, $trace);
        }

        $toolModel = AiTool::query()->where('slug', $slug)->first();

        if ($toolModel === null) {
            throw new ToolExecutionException("Tool [{$slug}] is not catalogued.");
        }

        if ($toolModel->status->value !== 'active') {
            throw new ToolExecutionException("Tool [{$slug}] is not active.");
        }

        $authorization = $this->authorize($toolModel, $context);

        $tool = $this->resolver->resolve($slug);
        $startedAt = microtime(true);

        try {
            if (! $authorization['approved']) {
                throw new ToolExecutionException($authorization['message']);
            }

            $rawResult = $tool->execute(array_merge($payload, ['_tool_context' => $context]));
            $result = $rawResult instanceof ToolResult ? $rawResult : new ToolResult(success: true, payload: (array) $rawResult);

            $executionResult = new ToolExecutionResult(
                tool: $slug,
                success: $result->success,
                input: $payload,
                output: $result->payload,
                error: $result->message,
                durationMs: (int) ((microtime(true) - $startedAt) * 1000),
                metadata: array_merge($result->metadata, ['authorization' => $authorization]),
            );
        } catch (\Throwable $exception) {
            $executionResult = new ToolExecutionResult(
                tool: $slug,
                success: false,
                input: $payload,
                output: [],
                error: $exception->getMessage(),
                durationMs: (int) ((microtime(true) - $startedAt) * 1000),
                metadata: ['authorization' => $authorization],
            );
        }

        ToolExecutionLog::query()->create([
            'ai_tool_id' => $toolModel->id,
            'conversation_id' => $context->conversation?->id,
            'execution_trace_id' => $trace?->id,
            'user_id' => $context->user?->id,
            'tool_name' => $slug,
            'status' => $executionResult->success ? 'completed' : 'failed',
            'duration_ms' => $executionResult->durationMs,
            'input_payload' => $executionResult->input,
            'output_payload' => $executionResult->output,
            'error_message' => $executionResult->error,
            'authorization_payload' => $authorization,
            'metadata' => $executionResult->metadata,
        ]);

        if (! $executionResult->success) {
            throw new ToolExecutionException($executionResult->error ?? "Tool [{$slug}] failed.");
        }

        return $executionResult;
    }

    private function executeEnterpriseTool(\App\Domains\Intelligence\Tools\Models\EnterpriseTool $tool, array $payload, ToolContext $context, ?ExecutionTrace $trace): ToolExecutionResult
    {
        $authorization = $this->toolAuthorization->authorize($tool, $context);

        if (! $authorization['approved']) {
            throw new ToolExecutionException("User is not authorized to execute [{$tool->slug}].");
        }

        try {
            $sandboxed = $this->sandbox->run(
                $tool->slug,
                fn (): array => $this->connectorManager->execute($tool, $payload, $context),
                [
                    'timeout_ms' => ((int) ($tool->security_policy['timeout_seconds'] ?? 10)) * 1000,
                    'execution_limit' => (int) ($tool->security_policy['execution_limit'] ?? 100),
                ],
            );

            $execution = new ToolExecutionData(
                toolSlug: $tool->slug,
                success: true,
                input: $payload,
                output: is_array($sandboxed['result']) ? $sandboxed['result'] : ['result' => $sandboxed['result']],
                error: null,
                durationMs: (int) $sandboxed['duration_ms'],
                metadata: [
                    'authorization' => $authorization,
                    'connector' => $tool->connector_type,
                    'stream' => ['Starting...', 'Executing...', 'Completed'],
                ],
            );
        } catch (\Throwable $exception) {
            $execution = new ToolExecutionData(
                toolSlug: $tool->slug,
                success: false,
                input: $payload,
                output: [],
                error: $exception->getMessage(),
                durationMs: 0,
                metadata: [
                    'authorization' => $authorization,
                    'connector' => $tool->connector_type,
                    'stream' => ['Starting...', 'Failed'],
                ],
            );
        }

        $this->usageTracker->record($tool, $execution, $context, $trace);
        $this->healthMonitor->record($tool, $execution->success, $execution->durationMs);
        $this->writeLegacyExecutionLog($tool->slug, $payload, $execution, $context, $trace);

        if (! $execution->success) {
            throw new ToolExecutionException($execution->error ?? "Tool [{$tool->slug}] failed.");
        }

        return new ToolExecutionResult(
            tool: $tool->slug,
            success: true,
            input: $execution->input,
            output: $execution->output,
            error: null,
            durationMs: $execution->durationMs,
            metadata: $execution->metadata,
        );
    }

    private function writeLegacyExecutionLog(string $slug, array $payload, ToolExecutionData $execution, ToolContext $context, ?ExecutionTrace $trace): void
    {
        $toolModel = AiTool::query()->where('slug', $slug)->first();

        ToolExecutionLog::query()->create([
            'ai_tool_id' => $toolModel?->id,
            'conversation_id' => $context->conversation?->id,
            'execution_trace_id' => $trace?->id,
            'user_id' => $context->user?->id,
            'tool_name' => $slug,
            'status' => $execution->success ? 'completed' : 'failed',
            'duration_ms' => $execution->durationMs,
            'input_payload' => $payload,
            'output_payload' => $execution->output,
            'error_message' => $execution->error,
            'authorization_payload' => $execution->metadata['authorization'] ?? [],
            'metadata' => $execution->metadata,
        ]);
    }

    private function authorize(AiTool $toolModel, ToolContext $context): array
    {
        $approved = $this->approvalService->isApproved($toolModel, $context->user);
        $hasPermission = $toolModel->permission_key === null
            || ($context->user?->can($toolModel->permission_key) ?? false)
            || ($context->user?->hasRole('administrator') ?? false);
        $organizationOk = $context->user === null
            || $context->conversation === null
            || ! isset($context->conversation->metadata['organization_id'])
            || $context->conversation->metadata['organization_id'] === $context->user->organization_id;

        if (! $approved) {
            return [
                'approved' => false,
                'status' => 'approval_required',
                'message' => "Tool [{$toolModel->slug}] requires approval before execution.",
            ];
        }

        if (! $hasPermission) {
            return [
                'approved' => false,
                'status' => 'permission_denied',
                'message' => "User is not authorized to execute [{$toolModel->slug}].",
            ];
        }

        if (! $organizationOk) {
            return [
                'approved' => false,
                'status' => 'organization_scope_mismatch',
                'message' => "Tool [{$toolModel->slug}] is outside the current organization scope.",
            ];
        }

        return [
            'approved' => true,
            'status' => 'authorized',
            'message' => 'Authorized.',
        ];
    }
}
