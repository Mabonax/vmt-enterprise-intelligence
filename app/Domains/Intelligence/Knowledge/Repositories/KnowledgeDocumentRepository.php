<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Repositories;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;

class KnowledgeDocumentRepository
{
    public function create(array $attributes): KnowledgeDocument
    {
        return KnowledgeDocument::query()->create($attributes);
    }

    public function find(string $id): ?KnowledgeDocument
    {
        return KnowledgeDocument::query()->find($id);
    }
}
