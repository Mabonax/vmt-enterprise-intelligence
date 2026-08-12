<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\Agent;
use Illuminate\Support\Collection;

class AgentManager
{
    public function all(): Collection
    {
        return Agent::query()->orderBy('name')->get();
    }

    public function create(array $attributes): Agent
    {
        return Agent::query()->create($attributes);
    }

    public function update(Agent $agent, array $attributes): Agent
    {
        $agent->fill($attributes)->save();

        return $agent->refresh();
    }
}
