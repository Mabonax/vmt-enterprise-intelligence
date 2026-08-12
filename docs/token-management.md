# Token Management

`TokenManager` keeps the runtime provider-neutral.

## Responsibilities

- Estimate prompt size through the configured `TokenCounter`
- Estimate completion size
- Prevent context overflow
- Trim history to fit the configured context window
- Provide consistent usage statistics helpers

## Extension Path

When a real provider requires model-specific counting, update that provider implementation without changing the rest of the runtime.
