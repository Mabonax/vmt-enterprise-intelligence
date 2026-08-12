<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Models;

use App\Domains\Intelligence\Enums\PromptTemplateStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string|null $scope
 * @property int $version
 * @property string|null $system_prompt
 * @property string|null $developer_prompt
 * @property string|null $user_prompt_template
 * @method static \Illuminate\Database\Eloquent\Builder<self> query()
 */
class PromptTemplate extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'owner_user_id',
        'organization_id',
        'scope',
        'name',
        'slug',
        'description',
        'category',
        'version',
        'approval_status',
        'status',
        'system_prompt',
        'developer_prompt',
        'user_prompt_template',
        'variables_schema',
        'output_schema',
        'is_default',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => PromptTemplateStatus::class,
            'variables_schema' => 'array',
            'output_schema' => 'array',
            'metadata' => 'array',
            'is_default' => 'bool',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
