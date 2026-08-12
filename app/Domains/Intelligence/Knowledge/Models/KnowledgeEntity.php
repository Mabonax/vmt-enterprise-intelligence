<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnowledgeEntity extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'knowledge_document_id',
        'entity_type',
        'name',
        'normalized_name',
        'confidence_score',
        'occurrences',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
