# Phase 6 RAG Architecture

Phase 6 introduces a lightweight RAG path without disrupting the earlier planner, verifier, connector, or prompt contracts.

## Flow

1. User prompt reaches `AgentExecutionService`.
2. `ContextAssembler` calls `KnowledgeRetrievalService` when `intelligence.knowledge.retrieval.enabled` is true.
3. `KnowledgeRetrievalService` delegates to `SemanticSearchService`.
4. Search results are written into `PromptContext.runtimeContext.payloads.knowledge`.
5. `PromptAssembler` continues emitting the runtime context as part of the system messages.

## Why This Shape

This preserves backward compatibility because existing prompt assembly still works even when knowledge retrieval is disabled or when no results exist.
