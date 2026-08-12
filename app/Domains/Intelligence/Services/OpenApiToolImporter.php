<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Tools\DTOs\ToolManifestData;

class OpenApiToolImporter
{
    /**
     * @param array<string, mixed> $document
     * @return list<ToolManifestData>
     */
    public function import(array $document): array
    {
        $server = $document['servers'][0]['url'] ?? '';
        $tools = [];

        foreach (($document['paths'] ?? []) as $path => $operations) {
            if (! is_array($operations)) {
                continue;
            }

            foreach ($operations as $method => $operation) {
                if (! is_array($operation)) {
                    continue;
                }

                $slug = strtolower($method).'_'.trim(str_replace(['/', '{', '}'], ['_', '', ''], $path), '_');
                $tools[] = new ToolManifestData(
                    name: (string) ($operation['summary'] ?? $slug),
                    slug: preg_replace('/[^a-z0-9_]/', '_', $slug) ?? $slug,
                    description: (string) ($operation['description'] ?? $operation['summary'] ?? 'Imported from OpenAPI'),
                    category: 'OpenAPI',
                    version: (string) ($document['info']['version'] ?? '1.0.0'),
                    connector: 'openapi',
                    permissions: ['intelligence.execute'],
                    inputs: ['parameters' => $operation['parameters'] ?? []],
                    outputs: ['responses' => $operation['responses'] ?? []],
                    metadata: [
                        'method' => strtoupper((string) $method),
                        'endpoint' => rtrim((string) $server, '/').$path,
                        'source' => 'openapi',
                    ],
                );
            }
        }

        return $tools;
    }
}
