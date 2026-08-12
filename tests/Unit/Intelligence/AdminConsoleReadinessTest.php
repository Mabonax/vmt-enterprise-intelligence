<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleReadinessService;
use Database\Seeders\IntelligenceCommercialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConsoleReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_returns_blockers_and_recommendations(): void
    {
        $this->seed(IntelligenceCommercialSeeder::class);

        $readiness = app(AdminConsoleReadinessService::class)->summary();

        $this->assertArrayHasKey('blockers', $readiness);
        $this->assertArrayHasKey('recommendations', $readiness);
        $this->assertIsArray($readiness['blockers']);
        $this->assertIsArray($readiness['recommendations']);
    }
}
