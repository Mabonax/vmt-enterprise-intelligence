<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use App\Domains\Intelligence\Knowledge\Enums\KnowledgeSourceType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnowledgeSource extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['document_collection_id', 'source_type', 'source_id', 'title', 'uri', 'status', 'metadata'];

    protected function casts(): array
    {
        return [
            'source_type' => KnowledgeSourceType::class,
            'metadata' => 'array',
        ];
    }
}
