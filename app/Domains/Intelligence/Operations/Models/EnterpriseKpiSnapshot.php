<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

class EnterpriseKpiSnapshot extends OperationsRecord
{
    protected $fillable = [
        'snapshot_key',
        'recorded_at',
        'kpi_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'kpi_payload' => 'array',
            'metadata' => 'array',
        ];
    }
}
