# Phase 6 Knowledge Graph

The knowledge graph persists graph nodes and edges independently from relationship records.

## Persistence

- `knowledge_graph_nodes`: stable node identities such as documents and entities.
- `knowledge_graph_edges`: graph links with relationship names and confidence.
- `knowledge_relationships`: broader relationship records useful outside graph traversal.

## Service Layer

`KnowledgeGraphService` provides node upsert, edge creation, and traversal. This is the additive seam for future graph-native planners, search ranking, and visual explorers.
