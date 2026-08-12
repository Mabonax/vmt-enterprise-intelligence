<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeploymentEvidence extends CommercialRecord
{
    protected $fillable =         array (
          0 => 'deployment_runbook_id',
          1 => 'deployment_step_id',
          2 => 'evidence_type',
          3 => 'label',
          4 => 'path',
          5 => 'metadata',
        );

    protected function casts(): array
    {
        return         array (
          'metadata' => 'array',
        );
    }

}