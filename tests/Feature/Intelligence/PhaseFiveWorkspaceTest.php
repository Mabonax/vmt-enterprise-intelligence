<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFiveWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_and_connector_pages_render(): void
    {
        $this->seed(AdministratorSeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('intelligence.marketplace'))
            ->assertOk()
            ->assertSee('Marketplace');

        $this->actingAs($user)
            ->get(route('intelligence.connectors'))
            ->assertOk()
            ->assertSee('Connectors');
    }
}
