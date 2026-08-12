<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnowledgeSearchLog extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['user_id', 'query', 'workspace', 'latency_ms', 'result_count', 'filters', 'metadata'];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'metadata' => 'array',
        ];
    }
}
