<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Models\ModelRoutingRule;
use App\Domains\Intelligence\Models\PromptTemplate;
use App\Domains\Intelligence\Services\ToolRegistry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class IntelligenceRuntimeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'intelligence.manage',
            'intelligence.execute',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $administrator = User::query()->where('email', env('VIP_ADMIN_EMAIL', 'admin@vip.local'))->first();

        if ($administrator !== null && ! $administrator->can('intelligence.manage')) {
            $administrator->givePermissionTo(['intelligence.manage', 'intelligence.execute']);
        }

        Agent::query()->firstOrCreate(
            ['slug' => 'executive-assistant'],
            [
                'owner_user_id' => $administrator?->id,
                'organization_id' => $administrator?->organization_id,
                'name' => 'Executive Assistant',
                'description' => 'Default enterprise runtime agent.',
                'purpose' => 'Coordinate safe provider-neutral runtime execution.',
                'status' => 'active',
                'visibility' => 'global',
                'default_provider' => config('intelligence.default_provider'),
                'default_model' => config('intelligence.default_model'),
                'system_instructions' => 'Act as a provider-neutral enterprise operating assistant.',
                'allowed_tools' => ['current_datetime', 'platform_status', 'conversation_summary'],
                'memory_enabled' => true,
            ],
        );

        PromptTemplate::query()->firstOrCreate(
            ['slug' => 'enterprise-runtime', 'version' => 1],
            [
                'owner_user_id' => $administrator?->id,
                'name' => 'Enterprise Runtime',
                'scope' => 'system',
                'category' => 'runtime',
                'approval_status' => 'approved',
                'status' => 'active',
                'is_default' => true,
                'system_prompt' => 'You are the VMT Enterprise Intelligence runtime.',
                'developer_prompt' => 'Honor planning, verification, permission, and audit constraints.',
                'user_prompt_template' => '{{prompt}}',
            ],
        );

        ModelRoutingRule::query()->firstOrCreate(
            ['provider' => config('intelligence.default_provider'), 'model' => config('intelligence.default_model'), 'capability' => 'chat'],
            [
                'priority' => 1,
                'max_context_tokens' => (int) config('intelligence.token_limits.max_context'),
                'cost_tier' => 'standard',
                'enabled' => true,
                'fallback_provider' => config('intelligence.model_routing.fallback.provider'),
                'fallback_model' => config('intelligence.model_routing.fallback.model'),
            ],
        );

        app(ToolRegistry::class)->syncDiscoveredTools();
    }
}
