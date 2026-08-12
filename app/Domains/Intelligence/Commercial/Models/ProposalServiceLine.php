<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProposalServiceLine extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_proposal_id',
          1 => 'line_label',
          2 => 'service_type',
          3 => 'quantity',
          4 => 'unit_price',
          5 => 'line_total',
          6 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'quantity' => 'decimal:2',
          'unit_price' => 'decimal:2',
          'line_total' => 'decimal:2',
          'metadata' => 'array',
        );
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(IntelligenceProposal::class, 'intelligence_proposal_id');
    }

}