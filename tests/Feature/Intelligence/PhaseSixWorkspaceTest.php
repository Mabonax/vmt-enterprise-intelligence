<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSixWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_workspace_pages_render(): void
    {
        $this->seed(AdministratorSeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('intelligence.knowledge-dashboard'))
            ->assertOk()
            ->assertSee('Knowledge Dashboard');

        $this->actingAs($user)
            ->get(route('intelligence.document-library'))
            ->assertOk()
            ->assertSee('Document Library');
    }
}
