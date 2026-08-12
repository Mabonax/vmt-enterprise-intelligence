<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProposalDeploymentLine extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_proposal_id',
          1 => 'line_label',
          2 => 'deployment_mode',
          3 => 'line_total',
          4 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'line_total' => 'decimal:2',
          'metadata' => 'array',
        );
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(IntelligenceProposal::class, 'intelligence_proposal_id');
    }

}