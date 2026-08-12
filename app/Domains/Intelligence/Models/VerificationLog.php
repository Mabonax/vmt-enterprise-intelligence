<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\ExecutionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationLog extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'execution_trace_id',
        'conversation_id',
        'status',
        'confidence_score',
        'checks',
        'missing_information',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExecutionStatus::class,
            'checks' => 'array',
            'missing_information' => 'array',
            'metadata' => 'array',
            'confidence_score' => 'float',
        ];
    }
}
