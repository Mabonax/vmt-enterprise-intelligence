<?php

declare(strict_types=1);

namespace App\Http\Requests\Gateway;

use Illuminate\Foundation\Http\FormRequest;

class GatewayClientManagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gateway_tenant_id' => ['required', 'uuid'],
            'organization_id' => ['nullable', 'uuid'],
            'connected_erp_id' => ['nullable', 'uuid'],
            'client_type' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:100'],
            'environment' => ['nullable', 'string', 'max:100'],
            'allowed_origins' => ['nullable', 'array'],
            'allowed_ips' => ['nullable', 'array'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1'],
            'rate_limit_per_hour' => ['nullable', 'integer', 'min:1'],
            'rate_limit_per_day' => ['nullable', 'integer', 'min:1'],
            'daily_quota' => ['nullable', 'integer', 'min:1'],
            'concurrent_requests' => ['nullable', 'integer', 'min:1'],
            'burst_limit' => ['nullable', 'integer', 'min:1'],
            'enabled_providers' => ['nullable', 'array'],
            'enabled_models' => ['nullable', 'array'],
            'enabled_capabilities' => ['nullable', 'array'],
            'scopes' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
            'policy' => ['nullable', 'array'],
        ];
    }
}
