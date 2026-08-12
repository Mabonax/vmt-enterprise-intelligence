<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentAvailability extends OperationsRecord
{
    protected $table = 'operations_agent_availability';

    protected $fillable = [
        'agent_catalog_id',
        'availability_status',
        'capacity_percentage',
        'available_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'capacity_percentage' => 'decimal:2',
            'available_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(AgentCatalog::class, 'agent_catalog_id');
    }
}
