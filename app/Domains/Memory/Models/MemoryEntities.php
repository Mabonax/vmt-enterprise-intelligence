<?php

declare(strict_types=1);

namespace App\Domains\Memory\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents stored memory records for agents, sessions, or organizations.
 */
class MemoryEntry extends Model
{
    protected $table = 'memory_entries';

    protected $guarded = [];
}
