<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ToolCredential extends Model
{
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'encrypted_payload',
        'last_rotated_at',
        'expires_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'last_rotated_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
