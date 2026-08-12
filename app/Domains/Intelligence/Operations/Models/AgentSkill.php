<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentSkill extends OperationsRecord
{
    protected $table = 'operations_agent_skills';

    protected $fillable = [
        'agent_catalog_id',
        'skill_key',
        'skill_level',
        'language',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(AgentCatalog::class, 'agent_catalog_id');
    }
}
