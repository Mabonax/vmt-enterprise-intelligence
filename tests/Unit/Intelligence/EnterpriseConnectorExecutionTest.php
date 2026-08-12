<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Services\ToolExecutor;
use App\Domains\Intelligence\Testing\LaravelToolExecutionProbe;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseConnectorExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_tool_executor_can_run_a_laravel_connector_tool_and_capture_phase_five_metrics(): void
    {
        $user = User::factory()->create();

        $tool = EnterpriseTool::query()->create([
            'name' => 'Laravel Probe',
            'slug' => 'laravel_probe',
            'description' => 'Probe the connector runtime.',
            'connector_type' => 'laravel',
            'version' => '1.0.0',
            'status' => 'active',
            'permissions' => [],
            'security_policy' => ['timeout_seconds' => 10, 'execution_limit' => 5],
            'metadata' => [
                'target' => [
                    'service' => LaravelToolExecutionProbe::class,
                    'method' => 'inspect',
                ],
            ],
        ]);

        $result = app(ToolExecutor::class)->execute('laravel_probe', ['tenant' => 'demo'], new ToolContext($user));

        $this->assertTrue($result->success);
        $this->assertSame('ok', $result->output['status']);
        $this->assertDatabaseHas('enterprise_tool_executions', [
            'enterprise_tool_id' => $tool->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('tool_usage', [
            'enterprise_tool_id' => $tool->id,
            'success' => true,
        ]);
        $this->assertDatabaseHas('tool_health', [
            'enterprise_tool_id' => $tool->id,
        ]);
        $this->assertDatabaseHas('execution_streams', [
            'message' => 'Completed',
        ]);
    }
}
