<?php

declare(strict_types=1);

namespace App\Http\Requests\Gateway;

use App\Domains\Intelligence\Gateway\DTOs\GatewayActorData;
use App\Domains\Intelligence\Gateway\DTOs\GatewayCapabilityRequestData;
use App\Domains\Intelligence\Gateway\DTOs\GatewaySubjectData;
use Illuminate\Foundation\Http\FormRequest;

class GatewayCapabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'uuid'],
            'erp_system' => ['required', 'string', 'max:150'],
            'correlation_id' => ['required', 'string', 'max:191'],
            'actor.id' => ['required', 'string', 'max:191'],
            'actor.roles' => ['nullable', 'array'],
            'actor.roles.*' => ['string', 'max:150'],
            'actor.permissions' => ['nullable', 'array'],
            'actor.permissions.*' => ['string', 'max:191'],
            'actor.metadata' => ['nullable', 'array'],
            'subject.type' => ['nullable', 'string', 'max:150'],
            'subject.id' => ['nullable', 'string', 'max:191'],
            'subject.metadata' => ['nullable', 'array'],
            'prompt' => ['required', 'string'],
            'context' => ['nullable', 'array'],
            'knowledge_references' => ['nullable', 'array'],
            'knowledge_references.*' => ['string', 'max:191'],
            'scopes' => ['nullable', 'array'],
            'scopes.*' => ['string', 'max:191'],
            'actions' => ['nullable', 'array'],
            'actions.*.tool' => ['required_with:actions', 'string', 'max:191'],
            'actions.*.payload' => ['nullable', 'array'],
            'options' => ['nullable', 'array'],
            'options.allow_actions' => ['nullable', 'boolean'],
            'options.stream' => ['nullable', 'boolean'],
            'options.model' => ['nullable', 'string', 'max:191'],
            'options.provider' => ['nullable', 'string', 'max:191'],
        ];
    }

    public function toData(string $capability): GatewayCapabilityRequestData
    {
        $validated = $this->validated();

        return new GatewayCapabilityRequestData(
            capability: $capability,
            organizationId: (string) $validated['organization_id'],
            erpSystem: (string) $validated['erp_system'],
            correlationId: (string) $validated['correlation_id'],
            actor: new GatewayActorData(
                id: (string) $validated['actor']['id'],
                roles: array_values($validated['actor']['roles'] ?? []),
                permissions: array_values($validated['actor']['permissions'] ?? []),
                metadata: $validated['actor']['metadata'] ?? [],
            ),
            subject: isset($validated['subject']['type'], $validated['subject']['id'])
                ? new GatewaySubjectData(
                    type: (string) $validated['subject']['type'],
                    id: (string) $validated['subject']['id'],
                    metadata: $validated['subject']['metadata'] ?? [],
                )
                : null,
            prompt: (string) $validated['prompt'],
            context: $validated['context'] ?? [],
            knowledgeReferences: array_values($validated['knowledge_references'] ?? []),
            actions: $validated['actions'] ?? [],
            options: array_merge($validated['options'] ?? [], [
                'requested_scopes' => array_values($validated['scopes'] ?? []),
            ]),
        );
    }
}
