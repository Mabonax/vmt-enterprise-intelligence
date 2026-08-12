<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Support\Navigation\VipNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminConsoleNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_includes_admin_console_without_removing_existing_intelligence_groups(): void
    {
        $items = collect(VipNavigation::items());

        $this->assertTrue($items->contains(fn (array $item): bool => $item['route'] === 'intelligence.admin-console.index'));
        $this->assertTrue($items->contains(fn (array $item): bool => $item['route'] === 'intelligence.commercial.packages'));
        $this->assertTrue($items->contains(fn (array $item): bool => $item['route'] === 'intelligence.operations.executive-dashboard'));
    }
}
