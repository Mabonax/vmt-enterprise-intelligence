<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use App\Domains\Intelligence\Knowledge\Enums\KnowledgeMemoryType;
use App\Domains\Intelligence\Knowledge\Enums\KnowledgeVisibility;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeMemory extends Model
{
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'knowledge_document_id',
        'memory_type',
        'title',
        'summary',
        'content',
        'owner_type',
        'owner_id',
        'tenant_id',
        'visibility',
        'classification',
        'importance',
        'confidence',
        'expiry_at',
        'embedding',
        'citations',
        'relationships',
        'created_from',
        'last_used_at',
        'usage_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'memory_type' => KnowledgeMemoryType::class,
            'visibility' => KnowledgeVisibility::class,
            'expiry_at' => 'datetime',
            'last_used_at' => 'datetime',
            'embedding' => 'array',
            'citations' => 'array',
            'relationships' => 'array',
            'created_from' => 'array',
            'metadata' => 'array',
        ];
    }
}
