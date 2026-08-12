<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnterpriseTool extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'category_id',
        'package_id',
        'name',
        'slug',
        'description',
        'connector_type',
        'handler_class',
        'version',
        'status',
        'publisher',
        'manifest_source',
        'input_schema',
        'output_schema',
        'permissions',
        'dependencies',
        'tags',
        'capabilities',
        'security_policy',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'input_schema' => 'array',
            'output_schema' => 'array',
            'permissions' => 'array',
            'dependencies' => 'array',
            'tags' => 'array',
            'capabilities' => 'array',
            'security_policy' => 'array',
            'metadata' => 'array',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ToolVersion::class);
    }
}
