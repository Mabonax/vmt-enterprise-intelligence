# Phase 6 Search Architecture

`SemanticSearchService` implements a hybrid retrieval strategy over enterprise knowledge.

## Signals

- keyword overlap against title, summary, and content
- cosine similarity between generated embeddings
- graph-density bonus from knowledge graph traversal

## Search Targets

- `knowledge_documents`
- `knowledge_memories`

The schema and service boundaries are intentionally broad enough to add workflow, trace, verification, and ERP entity retrieval later.
