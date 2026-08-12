<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeploymentRunbook extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'intelligence_tenant_id',
          1 => 'intelligence_proposal_id',
          2 => 'name',
          3 => 'status',
          4 => 'deployment_mode',
          5 => 'runbook_payload',
          6 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'runbook_payload' => 'array',
          'metadata' => 'array',
        );
    }


    public function steps(): HasMany
    {
        return $this->hasMany(DeploymentStep::class);
    }
}