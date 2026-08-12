<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiProviderProfile extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $table = 'ai_provider_profiles';

    protected $keyType = 'string';

    protected $fillable = [
        'provider_key',
        'display_name',
        'status',
        'capabilities',
        'configuration',
        'metadata',
        'last_checked_at',
        'last_latency_ms',
    ];

    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
            'configuration' => 'array',
            'metadata' => 'array',
            'last_checked_at' => 'datetime',
        ];
    }
}
