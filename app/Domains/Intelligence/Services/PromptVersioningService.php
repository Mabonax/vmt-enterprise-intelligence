<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Enums\PromptTemplateStatus;
use App\Domains\Intelligence\Models\PromptTemplate;

class PromptVersioningService
{
    public function createVersion(PromptTemplate $template, array $overrides = []): PromptTemplate
    {
        $attributes = array_merge(
            $template->withoutRelations()->toArray(),
            $overrides,
            [
                'version' => $template->version + 1,
                'status' => PromptTemplateStatus::Draft->value,
                'is_default' => false,
            ],
        );

        unset($attributes['id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);

        return PromptTemplate::query()->create($attributes);
    }
}
