<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\Models\PromptTemplate;
use App\Domains\Intelligence\Services\PromptTemplateRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Intelligence\PromptTemplateUpsertRequest;
use Illuminate\Http\RedirectResponse;

class PromptTemplateController extends Controller
{
    public function __construct(
        private readonly PromptTemplateRepository $templates,
    ) {}

    public function store(PromptTemplateUpsertRequest $request): RedirectResponse
    {
        $this->templates->create(array_merge(
            $request->validated(),
            ['owner_user_id' => $request->user()?->id],
        ));

        return back()->with('success', 'Prompt template created.');
    }

    public function update(PromptTemplateUpsertRequest $request, PromptTemplate $promptTemplate): RedirectResponse
    {
        $this->templates->update($promptTemplate, $request->validated());

        return back()->with('success', 'Prompt template updated.');
    }
}
