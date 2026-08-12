<?php

declare(strict_types=1);

namespace App\Domains\Knowledge\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a logical document collection boundary.
 */
class DocumentCollection extends Model
{
    protected $table = 'document_collections';

    protected $guarded = [];
}

/**
 * Represents a knowledge source record.
 */
class KnowledgeSource extends Model
{
    protected $table = 'knowledge_sources';

    protected $guarded = [];
}

/**
 * Represents chunk-level metadata prepared for embeddings.
 */
class KnowledgeChunk extends Model
{
    protected $table = 'knowledge_chunks';

    protected $guarded = [];
}
