<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Agents\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

abstract class SoftDeletingAgentRecord extends AgentRecord
{
    use SoftDeletes;
}
