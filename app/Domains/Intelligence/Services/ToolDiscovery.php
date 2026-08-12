<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Attributes\ToolDefinition;
use App\Domains\Intelligence\Contracts\IntelligenceTool;
use App\Domains\Intelligence\Tools\Attributes\EnterpriseTool as EnterpriseToolAttribute;
use App\Domains\Intelligence\Tools\DTOs\ToolManifestData;
use App\Domains\Intelligence\Tools\Support\ToolManifestParser;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ToolDiscovery
{
    public function __construct(
        private readonly Container $container,
        private readonly ToolManifestParser $manifestParser,
    ) {}

    /**
     * @param list<class-string<IntelligenceTool>> $toolClasses
     * @return list<ToolManifestData>
     */
    public function discover(array $toolClasses): array
    {
        $discovered = [];

        foreach ($toolClasses as $toolClass) {
            /** @var IntelligenceTool $tool */
            $tool = $this->container->make($toolClass);
            $reflection = new ReflectionClass($toolClass);
            $attribute = $reflection->getAttributes(EnterpriseToolAttribute::class)[0] ?? null;
            $legacy = $reflection->getAttributes(ToolDefinition::class)[0] ?? null;
            $enterprise = $attribute?->newInstance();
            $definition = $legacy?->newInstance();

            $discovered[] = new ToolManifestData(
                name: $enterprise?->name ?? $tool->name(),
                slug: $tool->slug(),
                description: $enterprise?->description ?? $definition?->description ?? $tool->description(),
                category: $enterprise?->category ?? $definition?->category ?? $tool->category(),
                version: $enterprise?->version ?? $definition?->version ?? '1.0.0',
                connector: $enterprise?->connector ?? 'laravel',
                handlerClass: $toolClass,
                author: 'Runtime discovery',
                enabled: $enterprise?->enabled ?? ! ($definition?->deprecated ?? false),
                permissions: $enterprise?->permissions ?? $tool->permissions(),
                tags: $enterprise?->tags ?? $definition?->tags ?? [],
                dependencies: [],
                inputs: $tool->schema(),
                outputs: $tool->responseSchema(),
                metadata: array_merge(
                    $enterprise?->metadata ?? [],
                    ['source' => 'attribute', 'provider' => $definition?->provider ?? 'internal'],
                ),
            );
        }

        $manifestDirectory = app_path('Domains/Intelligence/Tools/Manifests');

        if (File::isDirectory($manifestDirectory)) {
            foreach (File::files($manifestDirectory) as $file) {
                if ($file->getExtension() !== 'json') {
                    continue;
                }

                $discovered[] = $this->manifestParser->parseFile($file->getPathname());
            }
        }

        return $discovered;
    }
}
