<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConnectorHealth extends Model
{
    use HasUuids;

    protected $table = 'connector_health';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['connector_registration_id', 'status', 'latency_ms', 'last_checked_at', 'metadata'];

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
