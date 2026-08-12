# Prompt Pipeline

Prompt creation is centralized in `PromptAssembler`.

## Input Order

1. System prompt
2. Pinned context
3. Manual context
4. Tool definitions
5. Conversation history
6. User prompt

## Notes

- `ContextAssembler` is the only service that packages runtime context into a `PromptContext`.
- Future retrieval layers should enrich `manualContext` or `pinnedContext` rather than bypassing the assembler.
- Controllers and providers should not assemble prompts directly.
