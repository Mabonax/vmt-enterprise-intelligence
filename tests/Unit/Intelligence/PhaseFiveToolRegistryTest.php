<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Services\ConnectorManager;
use App\Domains\Intelligence\Services\ToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFiveToolRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase_five_registry_syncs_enterprise_tools_versions_and_connectors(): void
    {
        app(ToolRegistry::class)->syncDiscoveredTools();
        app(ConnectorManager::class)->syncRegistrations();

        $this->assertDatabaseHas('enterprise_tools', [
            'slug' => 'platform_status',
            'connector_type' => 'laravel',
        ]);
        $this->assertDatabaseHas('tool_versions', [
            'version' => '1.0.0',
            'is_current' => true,
        ]);
        $this->assertDatabaseHas('marketplace_packages', [
            'slug' => 'platform_status',
        ]);
        $this->assertDatabaseHas('connector_registrations', [
            'slug' => 'laravel',
        ]);
    }
}
