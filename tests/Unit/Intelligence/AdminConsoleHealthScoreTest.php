<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleHealthService;
use Database\Seeders\IntelligenceAdminConsoleSeeder;
use Database\Seeders\IntelligenceCommercialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConsoleHealthScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_score_returns_valid_status(): void
    {
        $this->seed([
            IntelligenceCommercialSeeder::class,
            IntelligenceAdminConsoleSeeder::class,
        ]);

        $service = app(AdminConsoleHealthService::class);
        $latest = $service->latest();

        $this->assertContains($latest['overall_status'], ['healthy', 'degraded', 'at_risk', 'critical', 'unknown']);
        $this->assertIsNumeric($latest['score']);
    }
}
