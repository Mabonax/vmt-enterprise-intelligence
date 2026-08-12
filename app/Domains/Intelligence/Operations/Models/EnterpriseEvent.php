<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Models;

class EnterpriseEvent extends OperationsRecord
{
    protected $fillable = [
        'enterprise_mission_id',
        'event_type',
        'event_key',
        'subject_type',
        'subject_id',
        'payload',
        'replayed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'replayed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
