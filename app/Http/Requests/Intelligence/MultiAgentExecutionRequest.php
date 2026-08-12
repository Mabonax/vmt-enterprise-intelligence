<?php

declare(strict_types=1);

namespace App\Http\Requests\Intelligence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MultiAgentExecutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'agent_id' => ['required', 'string', 'exists:agents,id'],
            'title' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'string'],
            'execution_mode' => ['nullable', Rule::in(['queued', 'immediate'])],
            'approval_role' => ['nullable', 'string', 'max:255'],
            'context' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
