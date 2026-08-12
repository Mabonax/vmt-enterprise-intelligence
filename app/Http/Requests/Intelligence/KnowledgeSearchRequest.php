<?php

declare(strict_types=1);

namespace App\Http\Requests\Intelligence;

use Illuminate\Foundation\Http\FormRequest;

class KnowledgeSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'max:500'],
            'workspace' => ['nullable', 'string', 'max:120'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:25'],
            'filters' => ['nullable', 'array'],
        ];
    }
}
