<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\AiToolStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property AiToolStatus $status
 * @property bool $requires_approval
 * @property string|null $permission_key
 */
class AiTool extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'handler_class',
        'input_schema',
        'output_schema',
        'status',
        'requires_approval',
        'permission_key',
        'timeout_seconds',
        'permissions',
        'tags',
        'version',
        'provider',
        'deprecated',
        'examples',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'input_schema' => 'array',
            'output_schema' => 'array',
            'permissions' => 'array',
            'tags' => 'array',
            'examples' => 'array',
            'metadata' => 'array',
            'requires_approval' => 'bool',
            'deprecated' => 'bool',
            'status' => AiToolStatus::class,
        ];
    }
}
