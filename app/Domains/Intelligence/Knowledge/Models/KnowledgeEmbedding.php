<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnowledgeEmbedding extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'knowledge_chunk_id',
        'provider',
        'model',
        'status',
        'vector',
        'dimensions',
        'checksum',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'vector' => 'array',
            'metadata' => 'array',
        ];
    }
}
