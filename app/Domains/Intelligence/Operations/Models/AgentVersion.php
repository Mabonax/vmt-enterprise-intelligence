<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentVersion extends OperationsRecord
{
    protected $table = 'operations_agent_versions';

    protected $fillable = [
        'agent_catalog_id',
        'version',
        'status',
        'released_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(AgentCatalog::class, 'agent_catalog_id');
    }
}
