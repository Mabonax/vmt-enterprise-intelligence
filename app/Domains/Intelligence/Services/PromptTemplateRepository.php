<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\PromptTemplate;
use Illuminate\Support\Collection;

class PromptTemplateRepository
{
    public function all(): Collection
    {
        return PromptTemplate::query()->orderBy('slug')->orderByDesc('version')->get();
    }

    public function create(array $attributes): PromptTemplate
    {
        return PromptTemplate::query()->create($attributes);
    }

    public function update(PromptTemplate $template, array $attributes): PromptTemplate
    {
        $template->fill($attributes)->save();

        return $template->refresh();
    }

    public function findActiveBySlug(string $slug)
    {
        return PromptTemplate::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->orderByDesc('version')
            ->first();
    }
}
