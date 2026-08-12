<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'knowledge_source_id',
        'knowledge_document_id',
        'content',
        'chunk_strategy',
        'position',
        'chunk_order',
        'token_count',
        'overlap',
        'checksum',
        'chunk_metadata',
        'embedding_metadata',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'chunk_metadata' => 'array',
            'embedding_metadata' => 'array',
            'metadata' => 'array',
        ];
    }
}
