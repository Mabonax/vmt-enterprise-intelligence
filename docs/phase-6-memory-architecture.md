# Phase 6 Memory Architecture

Knowledge memory now complements the earlier runtime memory layer instead of replacing it.

## Layers

- Existing runtime memory: `semantic_memories` and the earlier `MemoryRetriever` / `MemoryInjectionService`.
- New enterprise memory: `knowledge_memories` with memory type, importance, confidence, visibility, citations, relationships, embeddings, owner, tenant, and lifecycle metadata.

## Design Choice

The old memory path still powers current runtime behavior. The new knowledge-memory path provides a richer persistent store for long-term organizational memory, retrieval tuning, and later consolidation workflows.

## Consolidation

`MemoryConsolidationService` provides a scheduled duplicate-merging seam. This is intentionally simple and additive so later ranking logic can evolve without changing public runtime services.
