# Tool Framework

Tools are runtime-discoverable callables that must implement `App\Domains\Intelligence\Contracts\Tool`.

## Required Methods

- `name()`
- `description()`
- `schema()`
- `execute(array $payload): mixed`

## Runtime Support

- `ToolDispatcher` lists discovered tool definitions
- `tool_executions` persists future audit history
- No concrete business tools are included in Phase 2
