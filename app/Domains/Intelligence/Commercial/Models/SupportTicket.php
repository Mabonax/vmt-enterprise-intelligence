<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportTicket extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'support_plan_id',
          2 => 'title',
          3 => 'status',
          4 => 'severity',
          5 => 'assigned_to',
          6 => 'opened_at',
          7 => 'resolved_at',
          8 => 'ticket_payload',
          9 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'opened_at' => 'datetime',
          'resolved_at' => 'datetime',
          'ticket_payload' => 'array',
          'metadata' => 'array',
        );
    }

}