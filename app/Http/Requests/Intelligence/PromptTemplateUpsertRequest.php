<?php

declare(strict_types=1);

namespace App\Http\Requests\Intelligence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromptTemplateUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $template = $this->route('promptTemplate');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'version' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'system_prompt' => ['nullable', 'string'],
            'developer_prompt' => ['nullable', 'string'],
            'user_prompt_template' => ['nullable', 'string'],
            'variables_schema' => ['nullable', 'array'],
            'output_schema' => ['nullable', 'array'],
            'is_default' => ['boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
