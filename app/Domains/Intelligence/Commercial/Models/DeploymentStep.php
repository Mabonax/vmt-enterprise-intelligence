<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeploymentStep extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'deployment_runbook_id',
          1 => 'step_key',
          2 => 'title',
          3 => 'status',
          4 => 'sequence',
          5 => 'owner_name',
          6 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'metadata' => 'array',
        );
    }

    public function runbook(): BelongsTo
    {
        return $this->belongsTo(DeploymentRunbook::class, 'deployment_runbook_id');
    }

}