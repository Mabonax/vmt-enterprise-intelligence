<?php

namespace Tests\Feature\Intelligence;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ConnectedApplicationOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_create_real_organization_and_duplicate_code_is_rejected(): void
    {
        $operator = User::factory()->create();
        Permission::findOrCreate('intelligence.manage', 'web');
        $operator->givePermissionTo('intelligence.manage');
        $route = route('intelligence.admin-console.connected-applications.organizations.store');
        $this->actingAs($operator)->post($route, ['name' => 'Local clinic', 'code' => 'local-clinic'])->assertRedirect();
        $this->assertDatabaseHas('organizations', ['name' => 'Local clinic', 'code' => 'local-clinic', 'status' => 'active']);
        $this->post($route, ['name' => 'Duplicate', 'code' => 'local-clinic'])->assertSessionHasErrors('code');
        $this->assertDatabaseCount('organizations', 1);
    }

    public function test_non_operator_cannot_create_organization(): void
    {
        $this->actingAs(User::factory()->create())->post(route('intelligence.admin-console.connected-applications.organizations.store'), ['name' => 'Denied', 'code' => 'denied'])->assertForbidden();
        $this->assertDatabaseCount('organizations', 0);
    }
}
