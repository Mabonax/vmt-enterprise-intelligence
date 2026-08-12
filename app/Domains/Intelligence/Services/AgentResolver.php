<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Models\Conversation;
use App\Models\User;

class AgentResolver
{
    public function resolve(?Conversation $conversation = null, ?string $agentId = null, ?User $user = null)
    {
        if ($agentId !== null) {
            return Agent::query()->find($agentId);
        }

        if ($conversation?->agent_id !== null) {
            return Agent::query()->find($conversation->agent_id);
        }

        $defaultAgent = (string) config('intelligence.default_agent');

        if ($defaultAgent !== '') {
            return Agent::query()
                ->where('slug', $defaultAgent)
                ->orWhere('id', $defaultAgent)
                ->first();
        }

        return Agent::query()
            ->when($user !== null, fn ($query) => $query->where(function ($builder) use ($user): void {
                $builder->where('owner_user_id', $user->id)
                    ->orWhere('visibility', 'global')
                    ->orWhere('visibility', 'organization');
            }))
            ->where('status', 'active')
            ->orderBy('name')
            ->first();
    }
}
