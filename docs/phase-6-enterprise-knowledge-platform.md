# Phase 6 Enterprise Knowledge Platform

Phase 6 adds a new `App\Domains\Intelligence\Knowledge` bounded context on top of the existing Intelligence runtime. It remains strictly additive and preserves all Phase 1-5 interfaces.

## Delivered Capabilities

- Persistent knowledge storage for documents, chunks, embeddings, entities, relationships, graph nodes, graph edges, memories, collections, sources, sessions, events, feedback, learning cycles, and search logs.
- Queued ingestion pipeline for chunking, embeddings, entity extraction, relationship discovery, quality verification, and background maintenance.
- Hybrid retrieval through `SemanticSearchService` using keyword matching, embedding similarity, and graph-density signals.
- Knowledge retrieval integrated into `ContextAssembler` so prompt assembly can include enterprise knowledge without changing the `PromptAssembler` public interface.
- Learning-cycle capture integrated into `AgentExecutionService` after execution and verification complete.
- API endpoints and VIP workspace pages for knowledge search, documents, graph, memory, analytics, and health.

## Integration Points

1. `KnowledgeLifecycleService` creates document/source records and dispatches ingestion jobs.
2. `ContextAssembler` calls `KnowledgeRetrievalService` before prompt assembly when knowledge retrieval is enabled.
3. `AgentExecutionService` records learning-cycle signals after execution.
4. `routes/api.php` exposes knowledge endpoints behind `auth:sanctum`.
5. `routes/console.php` schedules cleanup, consolidation, learning, health, reindex, and verification jobs.
