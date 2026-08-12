<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportEscalation extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'support_ticket_id',
          1 => 'escalation_level',
          2 => 'status',
          3 => 'escalated_at',
          4 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'escalated_at' => 'datetime',
          'metadata' => 'array',
        );
    }

}