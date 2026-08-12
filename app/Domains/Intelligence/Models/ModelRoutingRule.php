<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\ModelCapability;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelRoutingRule extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'provider',
        'model',
        'capability',
        'priority',
        'max_context_tokens',
        'cost_tier',
        'enabled',
        'fallback_provider',
        'fallback_model',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'capability' => ModelCapability::class,
            'enabled' => 'bool',
            'metadata' => 'array',
        ];
    }
}
