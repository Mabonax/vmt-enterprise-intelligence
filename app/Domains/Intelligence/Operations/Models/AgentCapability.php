<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCapability extends OperationsRecord
{
    protected $table = 'operations_agent_capabilities';

    protected $fillable = [
        'agent_catalog_id',
        'capability_key',
        'capability_type',
        'confidence_score',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(AgentCatalog::class, 'agent_catalog_id');
    }
}
