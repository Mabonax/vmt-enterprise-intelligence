<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ToolExecutionLog extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'ai_tool_id',
        'conversation_id',
        'conversation_message_id',
        'execution_trace_id',
        'user_id',
        'tool_name',
        'status',
        'duration_ms',
        'input_payload',
        'output_payload',
        'error_message',
        'authorization_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'input_payload' => 'array',
            'output_payload' => 'array',
            'authorization_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(AiTool::class, 'ai_tool_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
