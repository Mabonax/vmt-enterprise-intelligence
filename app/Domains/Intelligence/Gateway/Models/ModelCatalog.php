<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelCatalog extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $table = 'model_catalog';

    protected $keyType = 'string';

    protected $fillable = [
        'provider_profile_id',
        'model_key',
        'category',
        'is_default',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
