<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleAlertService;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAlert;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAuditEvent;
use App\Models\User;
use Database\Seeders\IntelligenceAdminConsoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConsoleAlertWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_alerts_can_be_acknowledged_resolved_and_dismissed_with_audit_entries(): void
    {
        $this->seed(IntelligenceAdminConsoleSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo([
            'intelligence.admin_console.view',
            'intelligence.admin_console.alerts.manage',
        ]);

        $alert = app(AdminConsoleAlertService::class)->create([
            'alert_type' => 'runtime_warning',
            'severity' => 'warning',
            'title' => 'Runtime warning',
            'message' => 'Agent runtime requires operator attention.',
            'source_context' => 'operations',
            'source_type' => 'mission',
            'source_id' => 'mission-1',
        ]);

        $this->actingAs($user)
            ->post(route('intelligence.admin-console.alerts.acknowledge', $alert))
            ->assertRedirect();
        $this->assertSame('acknowledged', $alert->refresh()->status->value);

        $this->actingAs($user)
            ->post(route('intelligence.admin-console.alerts.resolve', $alert))
            ->assertRedirect();
        $this->assertSame('resolved', $alert->refresh()->status->value);

        $dismissed = app(AdminConsoleAlertService::class)->create([
            'alert_type' => 'support_noise',
            'severity' => 'info',
            'title' => 'Support noise',
            'message' => 'Low priority informational item.',
            'source_context' => 'support',
            'source_type' => 'queue',
            'source_id' => 'queue-1',
        ]);

        $this->actingAs($user)
            ->post(route('intelligence.admin-console.alerts.dismiss', $dismissed))
            ->assertRedirect();
        $this->assertSame('dismissed', $dismissed->refresh()->status->value);

        $this->assertGreaterThanOrEqual(3, AdminConsoleAuditEvent::query()->count());
    }

    public function test_duplicate_open_alerts_are_prevented(): void
    {
        $this->seed(IntelligenceAdminConsoleSeeder::class);

        $service = app(AdminConsoleAlertService::class);

        $first = $service->create([
            'alert_type' => 'duplicate_check',
            'severity' => 'critical',
            'title' => 'Duplicate check',
            'message' => 'Duplicate prevention test.',
            'source_context' => 'technical',
            'source_type' => 'connector',
            'source_id' => 'redis-main',
        ]);

        $second = $service->create([
            'alert_type' => 'duplicate_check',
            'severity' => 'critical',
            'title' => 'Duplicate check',
            'message' => 'Duplicate prevention test.',
            'source_context' => 'technical',
            'source_type' => 'connector',
            'source_id' => 'redis-main',
        ]);

        $this->assertTrue($first->is($second));
        $this->assertSame(
            1,
            AdminConsoleAlert::query()
                ->where('alert_type', 'duplicate_check')
                ->where('source_context', 'technical')
                ->where('source_type', 'connector')
                ->where('source_id', 'redis-main')
                ->count(),
        );
    }

    public function test_unauthorized_users_cannot_manage_alerts(): void
    {
        $this->seed(IntelligenceAdminConsoleSeeder::class);

        $user = User::factory()->create();
        $alert = app(AdminConsoleAlertService::class)->create([
            'alert_type' => 'unauthorized',
            'severity' => 'warning',
            'title' => 'Unauthorized test',
            'message' => 'Alert update must be forbidden.',
        ]);

        $this->actingAs($user)
            ->post(route('intelligence.admin-console.alerts.acknowledge', $alert))
            ->assertForbidden();
    }
}
