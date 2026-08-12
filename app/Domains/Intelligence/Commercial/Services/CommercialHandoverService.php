<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\CommercialHandover;
use App\Domains\Intelligence\Commercial\Models\IntelligenceProposal;

class CommercialHandoverService
{
    public function handover(IntelligenceProposal $proposal): CommercialHandover
    {
        return CommercialHandover::query()->create([
            'intelligence_proposal_id' => $proposal->id,
            'intelligence_tenant_id' => $proposal->intelligence_tenant_id,
            'handover_status' => 'ready',
            'handover_payload' => $proposal->payload,
            'completed_at' => now(),
            'metadata' => ['stage' => $proposal->stage],
        ]);
    }
}