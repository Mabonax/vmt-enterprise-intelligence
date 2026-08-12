# Provider Development Guide

Future provider integrations must implement `App\Domains\Intelligence\Contracts\AiProvider`.

## Required Methods

- `chat(ChatRequest $request): ChatResponse`
- `stream(ChatRequest $request): iterable`
- `embeddings(array $input): array`
- `models(): array`
- `health(): array`
- `estimateTokens(array|string $input): int`

## Rules

- Keep provider-specific code inside `app/Domains/Intelligence/Providers/<ProviderName>`
- Do not let controllers, pages, or domain services depend on SDK-specific response shapes
- Return runtime DTOs or normalized arrays only
- Preserve the provider key defined by `key()`

## Registration

Provider classes are auto-discovered by `IntelligenceServiceProvider`. If the class implements `AiProvider` and lives under the providers tree, it becomes available to the runtime.
