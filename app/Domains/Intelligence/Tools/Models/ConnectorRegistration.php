<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConnectorRegistration extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name', 'slug', 'driver', 'status', 'configuration', 'metadata'];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'metadata' => 'array',
        ];
    }
}
