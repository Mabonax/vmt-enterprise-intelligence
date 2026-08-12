<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderHealthCheck extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'provider_key',
        'status',
        'latency_ms',
        'available_models',
        'payload',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'checked_at' => 'datetime',
        ];
    }
}
