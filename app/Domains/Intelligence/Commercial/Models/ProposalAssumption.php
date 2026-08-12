<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProposalAssumption extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_proposal_id',
          1 => 'assumption',
          2 => 'status',
          3 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'metadata' => 'array',
        );
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(IntelligenceProposal::class, 'intelligence_proposal_id');
    }

}