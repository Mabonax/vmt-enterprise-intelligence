<?php

declare(strict_types=1);

namespace App\Http\Requests\Intelligence;

use Illuminate\Foundation\Http\FormRequest;

class KnowledgeIngestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'source_type' => ['required', 'string', 'max:80'],
            'mime_type' => ['required', 'string', 'max:120'],
            'workspace' => ['nullable', 'string', 'max:120'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
