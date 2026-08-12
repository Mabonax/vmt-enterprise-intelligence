<?php

declare(strict_types=1);

namespace App\Domains\PromptLibrary\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents versioned prompt templates across scopes.
 */
class PromptTemplate extends Model
{
    protected $table = 'prompt_templates';

    protected $guarded = [];
}
