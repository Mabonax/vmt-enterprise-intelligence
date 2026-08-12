<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class AdminConsoleRecord extends Model
{
    use HasFactory;

    protected $guarded = [];
}
