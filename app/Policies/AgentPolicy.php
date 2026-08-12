<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domains\Intelligence\Models\Agent;
use App\Models\User;

class AgentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('administrator') || $user->can('intelligence.manage');
    }

    public function view(User $user, Agent $agent): bool
    {
        return $this->viewAny($user)
            || $agent->owner_user_id === $user->id
            || ($agent->visibility->value === 'organization' && $agent->organization_id === $user->organization_id)
            || $agent->visibility->value === 'global';
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Agent $agent): bool
    {
        return $this->view($user, $agent);
    }
}
