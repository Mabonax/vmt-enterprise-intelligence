<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\VerificationResultData;
use App\Domains\Intelligence\Models\ExecutionTrace;
use App\Domains\Intelligence\Models\VerificationLog;

class VerificationEngine
{
    public function verify(ExecutionTrace $trace, array $stepPayloads, array $toolResults): VerificationResultData
    {
        $requiredTools = collect($trace->plan_payload['required_tools'] ?? []);
        $executedTools = collect($toolResults)->pluck('tool')->filter()->values();
        $failedSteps = collect($stepPayloads)->where('status', 'failed')->values();
        $missingTools = $requiredTools->diff($executedTools)->values()->all();

        $checks = [
            ['name' => 'plan_steps_recorded', 'passed' => count($stepPayloads) > 0],
            ['name' => 'tool_permissions_enforced', 'passed' => collect($stepPayloads)->every(fn (array $payload): bool => ($payload['authorization_status'] ?? 'authorized') !== 'bypassed')],
            ['name' => 'tool_results_available', 'passed' => $toolResults !== [] || $stepPayloads !== []],
            ['name' => 'required_tools_executed', 'passed' => $missingTools === []],
            ['name' => 'step_failures_recovered', 'passed' => $failedSteps->isEmpty()],
        ];

        $passed = collect($checks)->every(fn (array $check): bool => $check['passed'] === true);
        $confidence = 1.0
            - ($missingTools !== [] ? 0.25 : 0.0)
            - ($failedSteps->isNotEmpty() ? 0.20 : 0.0)
            - (count($stepPayloads) === 0 ? 0.20 : 0.0);
        $confidence = max(0.15, min(0.96, $confidence));

        $missing = [];

        if ($missingTools !== []) {
            $missing[] = 'Required tools were not executed: '.implode(', ', $missingTools);
        }

        if ($failedSteps->isNotEmpty()) {
            $missing[] = 'One or more execution steps failed and require follow-up.';
        }

        if ($missing === [] && $confidence < (float) config('intelligence.agent_runtime.verification_threshold', 0.75)) {
            $missing[] = 'Confidence is below the verification threshold.';
        }

        $passed = $passed && $confidence >= (float) config('intelligence.agent_runtime.verification_threshold', 0.75);

        $verificationLog = VerificationLog::query()->create([
            'execution_trace_id' => $trace->id,
            'conversation_id' => $trace->conversation_id,
            'status' => $passed ? 'completed' : 'needs_input',
            'confidence_score' => $confidence,
            'checks' => $checks,
            'missing_information' => $missing,
            'metadata' => [
                'tool_results' => count($toolResults),
                'failed_steps' => $failedSteps->count(),
                'missing_tools' => $missingTools,
            ],
        ]);

        $trace->forceFill([
            'verification_log_id' => $verificationLog->id,
        ])->save();

        return new VerificationResultData(
            passed: $passed,
            confidenceScore: $confidence,
            checks: $checks,
            missingInformation: $missing,
            metadata: ['verification_log_id' => $verificationLog->id],
        );
    }
}
