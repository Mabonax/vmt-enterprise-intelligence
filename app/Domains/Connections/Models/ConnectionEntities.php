<?php

declare(strict_types=1);

namespace App\Domains\Connections\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents a connected ERP client record.
 */
class ConnectedErp extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $table = 'connected_erps';

    protected $guarded = [];

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'allowed_models' => 'array',
            'permissions' => 'array',
            'rate_limits' => 'array',
            'metadata' => 'array',
        ];
    }
}

/**
 * Represents a stored connection API key record.
 */
class ErpApiKey extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $table = 'erp_api_keys';

    protected $guarded = [];

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}

/**
 * Represents an auditable connection log entry.
 */
class ConnectionLog extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $table = 'connection_logs';

    protected $guarded = [];

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'logged_at' => 'datetime',
        ];
    }
}
