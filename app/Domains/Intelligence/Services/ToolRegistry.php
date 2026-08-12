<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Attributes\ToolDefinition;
use App\Domains\Intelligence\Contracts\IntelligenceTool;
use App\Domains\Intelligence\Models\AiTool;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool as EnterpriseToolModel;
use App\Domains\Intelligence\Tools\Models\ToolCategory;
use App\Domains\Intelligence\Tools\Models\ToolDependency;
use App\Domains\Intelligence\Tools\Models\ToolPackage;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;

class ToolRegistry
{
    public function __construct(
        private readonly \Illuminate\Contracts\Container\Container $container,
        private readonly array $toolClasses,
        private readonly ToolDiscovery $toolDiscovery,
        private readonly ToolValidator $validator,
        private readonly ToolVersionManager $versionManager,
        private readonly ToolPublisher $publisher,
    ) {}

    public function syncDiscoveredTools(): void
    {
        foreach ($this->toolClasses as $toolClass) {
            /** @var IntelligenceTool $tool */
            $tool = $this->container->make($toolClass);
            $reflection = new ReflectionClass($toolClass);
            $attribute = $reflection->getAttributes(ToolDefinition::class)[0] ?? null;
            /** @var ToolDefinition|null $definition */
            $definition = $attribute?->newInstance();

            AiTool::query()->updateOrCreate(
                ['handler_class' => $toolClass],
                [
                    'name' => $tool->name(),
                    'slug' => $tool->slug(),
                    'description' => $definition instanceof ToolDefinition && $definition->description !== null ? $definition->description : $tool->description(),
                    'category' => $definition instanceof ToolDefinition ? $definition->category : $tool->category(),
                    'status' => $definition instanceof ToolDefinition && $definition->deprecated ? 'deprecated' : 'active',
                    'permission_key' => $tool->permissions()[0] ?? null,
                    'permissions' => $tool->permissions(),
                    'input_schema' => $tool->schema(),
                    'output_schema' => $tool->responseSchema(),
                    'requires_approval' => $definition instanceof ToolDefinition ? $definition->requiresApproval : false,
                    'timeout_seconds' => $definition instanceof ToolDefinition ? $definition->timeoutSeconds : 10,
                    'tags' => $definition instanceof ToolDefinition ? $definition->tags : [],
                    'version' => $definition instanceof ToolDefinition ? $definition->version : '1.0.0',
                    'provider' => $definition instanceof ToolDefinition ? $definition->provider : 'internal',
                    'deprecated' => $definition instanceof ToolDefinition ? $definition->deprecated : false,
                    'examples' => $definition instanceof ToolDefinition ? $definition->examples : [],
                    'metadata' => ['discovered' => true],
                ],
            );
        }

        if (! Schema::hasTable('enterprise_tools')) {
            return;
        }

        foreach ($this->toolDiscovery->discover($this->toolClasses) as $manifest) {
            $this->validator->validate($manifest);

            $category = ToolCategory::query()->updateOrCreate(
                ['slug' => str($manifest->category)->slug('_')->toString()],
                [
                    'name' => $manifest->category,
                    'description' => $manifest->category.' enterprise tools',
                    'metadata' => [],
                ],
            );

            $package = ToolPackage::query()->updateOrCreate(
                ['slug' => $manifest->slug],
                [
                    'name' => $manifest->name,
                    'version' => $manifest->version,
                    'publisher' => $manifest->author,
                    'status' => $manifest->enabled ? 'published' : 'disabled',
                    'metadata' => $manifest->metadata,
                ],
            );

            $tool = EnterpriseToolModel::query()->updateOrCreate(
                [
                    'slug' => $manifest->slug,
                    'version' => $manifest->version,
                ],
                [
                    'category_id' => $category->id,
                    'package_id' => $package->id,
                    'name' => $manifest->name,
                    'description' => $manifest->description,
                    'connector_type' => $manifest->connector,
                    'handler_class' => $manifest->handlerClass,
                    'status' => $manifest->enabled ? 'active' : 'disabled',
                    'publisher' => $manifest->author,
                    'manifest_source' => $manifest->handlerClass !== null ? 'attribute' : 'manifest',
                    'input_schema' => $manifest->inputs,
                    'output_schema' => $manifest->outputs,
                    'permissions' => $manifest->permissions,
                    'dependencies' => $manifest->dependencies,
                    'tags' => $manifest->tags,
                    'capabilities' => ['connector' => $manifest->connector],
                    'security_policy' => ['timeout_seconds' => 10, 'retry_limit' => 2],
                    'metadata' => $manifest->metadata,
                ],
            );

            ToolDependency::query()->where('enterprise_tool_id', $tool->id)->delete();

            foreach ($manifest->dependencies as $dependency) {
                if (! is_array($dependency) || ! isset($dependency['name'])) {
                    continue;
                }

                ToolDependency::query()->create([
                    'enterprise_tool_id' => $tool->id,
                    'dependency_slug' => (string) $dependency['name'],
                    'constraint' => (string) ($dependency['constraint'] ?? '*'),
                    'metadata' => $dependency,
                ]);
            }

            $this->versionManager->sync($tool, $manifest);
            $this->publisher->publish($tool);
        }
    }

    public function definitions(): array
    {
        return AiTool::query()->orderBy('name')->get()->all();
    }

    public function findEnterpriseTool(string $slug, ?string $version = null): ?EnterpriseToolModel
    {
        $query = EnterpriseToolModel::query()->where('slug', $slug)->orderByDesc('version');

        if ($version !== null) {
            $query->where('version', $version);
        }

        return $query->first();
    }

    public function enterpriseDefinitions(): array
    {
        return Schema::hasTable('enterprise_tools')
            ? EnterpriseToolModel::query()->orderBy('name')->get()->all()
            : [];
    }
}
