<?php

declare(strict_types=1);

namespace App\Http\Requests\Intelligence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnterpriseAgentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('agents', 'slug')],
            'description' => ['nullable', 'string'],
            'purpose' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'disabled', 'archived'])],
            'visibility' => ['nullable', Rule::in(['private', 'team', 'organization', 'global'])],
            'agent_role_key' => ['required', 'string', 'max:255'],
            'system_instructions' => ['nullable', 'string'],
            'default_provider' => ['nullable', 'string'],
            'default_model' => ['nullable', 'string'],
            'allowed_tools' => ['nullable', 'array'],
            'memory_enabled' => ['nullable', 'boolean'],
            'reasoning_style' => ['nullable', 'string', 'max:255'],
            'risk_tolerance' => ['nullable', 'string', 'max:255'],
            'verification_strategy' => ['nullable', 'string', 'max:255'],
            'memory_scope' => ['nullable', 'string', 'max:255'],
            'delegation_enabled' => ['nullable', 'boolean'],
            'approval_required' => ['nullable', 'boolean'],
            'capabilities' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
