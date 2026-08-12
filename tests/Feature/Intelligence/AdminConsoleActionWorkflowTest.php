<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAction;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAuditEvent;
use App\Models\User;
use Database\Seeders\IntelligenceAdminConsoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminConsoleActionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_actions_move_from_requested_to_approved_to_completed(): void
    {
        $this->seed(IntelligenceAdminConsoleSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo([
            'intelligence.admin_console.view',
            'intelligence.admin_console.actions.manage',
            'intelligence.admin_console.health.view',
        ]);
        Sanctum::actingAs($user);

        $actionResponse = $this->postJson(route('api.intelligence.admin-console.actions.store'), [
            'action_type' => 'capture_health_snapshot',
            'title' => 'Capture health snapshot',
            'description' => 'Test action workflow.',
            'target_context' => 'health',
            'payload' => ['requested_from' => 'test'],
        ])->assertCreated();

        $action = AdminConsoleAction::query()->findOrFail($actionResponse->json('action.id'));
        $this->assertSame('requested', $action->status->value);

        $this->actingAs($user)
            ->post(route('intelligence.admin-console.actions.approve', $action))
            ->assertRedirect();
        $this->assertSame('approved', $action->refresh()->status->value);

        $this->actingAs($user)
            ->post(route('intelligence.admin-console.actions.execute', $action))
            ->assertRedirect();
        $this->assertSame('completed', $action->refresh()->status->value);
        $this->assertGreaterThanOrEqual(3, AdminConsoleAuditEvent::query()->count());
    }

    public function test_unauthorized_users_cannot_manage_actions(): void
    {
        $this->seed(IntelligenceAdminConsoleSeeder::class);

        $user = User::factory()->create();
        $action = AdminConsoleAction::query()->create([
            'uuid' => (string) str()->uuid(),
            'action_type' => 'refresh_readiness',
            'title' => 'Refresh readiness',
            'status' => 'requested',
            'requested_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('intelligence.admin-console.actions.approve', $action))
            ->assertForbidden();
    }
}
