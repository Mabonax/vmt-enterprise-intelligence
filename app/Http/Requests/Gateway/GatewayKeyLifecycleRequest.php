<?php

declare(strict_types=1);

namespace App\Http\Requests\Gateway;

use Illuminate\Foundation\Http\FormRequest;

class GatewayKeyLifecycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'uuid'],
            'key_identifier' => ['nullable', 'string', 'max:191'],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
