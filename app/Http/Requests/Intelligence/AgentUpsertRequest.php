<?php

declare(strict_types=1);

namespace App\Http\Requests\Intelligence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgentUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $agent = $this->route('agent');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('agents', 'slug')->ignore($agent)],
            'description' => ['nullable', 'string'],
            'purpose' => ['nullable', 'string'],
            'system_instructions' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'active', 'disabled', 'archived'])],
            'visibility' => ['required', Rule::in(['private', 'team', 'organization', 'global'])],
            'default_provider' => ['nullable', 'string'],
            'default_model' => ['nullable', 'string'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
            'max_tokens' => ['nullable', 'integer', 'min:1'],
            'memory_enabled' => ['boolean'],
            'conversation_limit' => ['nullable', 'integer', 'min:1'],
            'allowed_tools' => ['nullable', 'array'],
            'allowed_knowledge_sources' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
