<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnowledgeFeedback extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['knowledge_session_id', 'user_id', 'rating', 'comment', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
