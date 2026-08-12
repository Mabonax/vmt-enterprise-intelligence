<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Support;

use App\Domains\Intelligence\Tools\DTOs\ToolManifestData;
use App\Domains\Intelligence\Tools\Exceptions\ToolManifestException;

class ToolManifestParser
{
    public function parseFile(string $path): ToolManifestData
    {
        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload)) {
            throw new ToolManifestException("Manifest [{$path}] is not valid JSON.");
        }

        foreach (['name', 'slug', 'description', 'version', 'category'] as $required) {
            if (! isset($payload[$required]) || ! is_string($payload[$required]) || $payload[$required] === '') {
                throw new ToolManifestException("Manifest [{$path}] is missing [{$required}].");
            }
        }

        return new ToolManifestData(
            name: $payload['name'],
            slug: $payload['slug'],
            description: $payload['description'],
            category: $payload['category'],
            version: $payload['version'],
            connector: (string) ($payload['connector'] ?? 'laravel'),
            handlerClass: isset($payload['handler_class']) ? (string) $payload['handler_class'] : null,
            author: isset($payload['author']) ? (string) $payload['author'] : null,
            enabled: (bool) ($payload['enabled'] ?? true),
            permissions: array_values($payload['permissions'] ?? []),
            tags: array_values($payload['tags'] ?? []),
            dependencies: array_values($payload['dependencies'] ?? []),
            inputs: is_array($payload['inputs'] ?? null) ? $payload['inputs'] : [],
            outputs: is_array($payload['outputs'] ?? null) ? $payload['outputs'] : [],
            metadata: is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [],
        );
    }
}
