<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ToolVersion extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'enterprise_tool_id',
        'version',
        'is_current',
        'compatibility_range',
        'manifest_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'bool',
            'manifest_payload' => 'array',
            'metadata' => 'array',
        ];
    }
}
