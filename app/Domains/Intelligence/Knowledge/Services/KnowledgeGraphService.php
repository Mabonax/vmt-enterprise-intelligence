<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeGraphEdge;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeGraphNode;

class KnowledgeGraphService
{
    public function upsertNode(string $type, string $nodeId, string $label, array $metadata = []): KnowledgeGraphNode
    {
        return KnowledgeGraphNode::query()->updateOrCreate(
            ['node_type' => $type, 'node_id' => $nodeId],
            ['label' => $label, 'metadata' => $metadata],
        );
    }

    public function link(KnowledgeGraphNode $source, KnowledgeGraphNode $target, string $relationship, float $confidence = 0.75, array $metadata = []): KnowledgeGraphEdge
    {
        return KnowledgeGraphEdge::query()->updateOrCreate(
            [
                'source_node_id' => $source->id,
                'target_node_id' => $target->id,
                'relationship' => $relationship,
            ],
            [
                'confidence_score' => $confidence,
                'metadata' => $metadata,
            ],
        );
    }

    public function traverse(string $nodeType, string $nodeId): array
    {
        $node = KnowledgeGraphNode::query()->where('node_type', $nodeType)->where('node_id', $nodeId)->first();

        if ($node === null) {
            return [];
        }

        return KnowledgeGraphEdge::query()
            ->where('source_node_id', $node->id)
            ->orWhere('target_node_id', $node->id)
            ->get()
            ->toArray();
    }
}
