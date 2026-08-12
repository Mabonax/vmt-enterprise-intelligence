<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeDocument extends Model
{
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'knowledge_collection_id',
        'knowledge_source_id',
        'title',
        'slug',
        'source_type',
        'mime_type',
        'language',
        'status',
        'visibility',
        'classification',
        'content',
        'summary',
        'keywords',
        'quality_score',
        'version',
        'checksum',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'metadata' => 'array',
        ];
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(KnowledgeChunk::class);
    }
}
