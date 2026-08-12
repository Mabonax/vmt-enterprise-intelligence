<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\DTOs\ToolExecutionResult;
use App\Domains\Intelligence\Models\ExecutionTrace;
use App\Domains\Intelligence\Services\VerificationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_flags_missing_required_tools(): void
    {
        $trace = ExecutionTrace::query()->create([
            'provider' => 'ollama',
            'model' => 'runtime-placeholder',
            'status' => 'running',
            'plan_payload' => [
                'required_tools' => ['current_datetime', 'platform_status'],
            ],
        ]);

        $result = app(VerificationEngine::class)->verify($trace, [
            ['status' => 'completed', 'authorization_status' => 'authorized'],
        ], [
            new ToolExecutionResult(tool: 'current_datetime', success: true),
        ]);

        $this->assertFalse($result->passed);
        $this->assertNotEmpty($result->missingInformation);
        $this->assertStringContainsString('platform_status', $result->missingInformation[0]);
    }
}
