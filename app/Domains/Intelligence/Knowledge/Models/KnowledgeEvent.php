<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KnowledgeEvent extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['event_type', 'subject_type', 'subject_id', 'payload', 'metadata'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'metadata' => 'array',
        ];
    }
}
