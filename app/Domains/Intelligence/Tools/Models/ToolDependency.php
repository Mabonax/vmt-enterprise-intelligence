<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ToolDependency extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['enterprise_tool_id', 'dependency_slug', 'constraint', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
