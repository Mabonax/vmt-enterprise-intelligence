<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Models\User;
use Database\Seeders\IntelligenceCommercialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhaseNineCommercialPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_nine_commercial_api_end_to_end_workflow_operates(): void
    {
        config()->set('deployment.commercial_console_enabled', true);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->seed(IntelligenceCommercialSeeder::class);

        $tenant = IntelligenceTenant::query()->where('slug', 'vmt-internal')->firstOrFail();
        $package = IntelligencePackage::query()->where('slug', 'starter-intelligence')->firstOrFail();

        $subscriptionResponse = $this->postJson('/api/intelligence/commercial/subscriptions', [
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
        ])->assertCreated();

        $subscriptionId = $subscriptionResponse->json('subscription.id');

        $this->postJson("/api/intelligence/commercial/usage/{$subscriptionId}", [
            'meter_key' => 'agent_executions',
            'quantity' => 450,
            'context' => ['source' => 'test'],
        ])->assertOk()
            ->assertJsonPath('usage.warning', true);

        $this->postJson("/api/intelligence/commercial/subscriptions/{$subscriptionId}/invoice")
            ->assertOk()
            ->assertJsonStructure(['invoice' => ['invoice_number', 'grand_total', 'lines']]);

        $proposal = $this->postJson('/api/intelligence/commercial/proposals', [
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'deployment_mode' => 'shared_saas',
        ])->assertCreated();

        $proposalId = $proposal->json('proposal.id');

        $this->postJson("/api/intelligence/commercial/proposals/{$proposalId}/stage", [
            'stage' => 'approved',
        ])->assertOk()
            ->assertJsonPath('proposal.stage', 'approved');
    }
}