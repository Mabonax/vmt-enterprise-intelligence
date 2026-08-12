<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Repositories;

use App\Domains\Intelligence\Agents\Models\AgentSession;
use Illuminate\Support\Collection;

class AgentSessionRepository
{
    public function latest(int $limit = 20): Collection
    {
        return AgentSession::query()->latest()->limit($limit)->get();
    }
}
