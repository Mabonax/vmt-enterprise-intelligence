<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Models\Conversation;
use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Database\Seeders\IntelligenceRuntimeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RuntimeExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_runtime_can_execute_a_prompt_through_the_agent_pipeline(): void
    {
        $this->seed([
            AdministratorSeeder::class,
            IntelligenceRuntimeSeeder::class,
        ]);

        $user = User::factory()->create();
        $user->givePermissionTo('intelligence.execute');

        $conversation = Conversation::query()->create([
            'user_id' => $user->id,
            'title' => 'Runtime execution',
            'provider' => 'ollama',
            'model' => 'runtime-placeholder',
            'status' => 'active',
        ]);

        Http::fake([
            'http://localhost:11434/api/chat' => Http::response([
                'model' => 'runtime-placeholder',
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Runtime execution response.',
                ],
                'done' => true,
                'total_duration' => 400000000,
                'prompt_eval_count' => 10,
                'eval_count' => 7,
            ]),
        ]);

        $response = $this->actingAs($user)->postJson(route('intelligence.runtime.execute'), [
            'conversation_id' => $conversation->id,
            'prompt' => 'Summarize the platform status.',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('verification.passed', true)
            ->assertJsonPath('status', 'completed');

        $this->assertDatabaseCount('execution_plans', 1);
        $this->assertDatabaseCount('execution_traces', 1);
        $this->assertDatabaseHas('execution_plans', [
            'completion_state' => 'fulfilled',
        ]);
        $this->assertDatabaseHas('tool_execution_logs', [
            'tool_name' => 'platform_status',
        ]);
        $this->assertDatabaseHas('tool_execution_logs', [
            'tool_name' => 'conversation_summary',
        ]);
    }
}
