<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExecutionStream extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['tool_execution_id', 'status', 'message', 'sequence', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
