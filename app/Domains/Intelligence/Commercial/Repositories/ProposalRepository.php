<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Repositories;

use App\Domains\Intelligence\Commercial\Models\IntelligenceProposal;
use Illuminate\Database\Eloquent\Collection;

class ProposalRepository
{
    public function latest(int $limit = 25): Collection
    {
        return IntelligenceProposal::query()->latest()->limit($limit)->get();
    }
}