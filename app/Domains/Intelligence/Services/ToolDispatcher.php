<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\Tool;
use App\Domains\Intelligence\Exceptions\ToolExecutionException;
use Illuminate\Contracts\Container\Container;

class ToolDispatcher
{
    /**
     * @param list<class-string<Tool>> $toolClasses
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $toolClasses = [],
    ) {}

    public function definitions(): array
    {
        return array_map(function (string $toolClass): array {
            /** @var Tool $tool */
            $tool = $this->container->make($toolClass);

            return [
                'name' => $tool->name(),
                'description' => $tool->description(),
                'schema' => $tool->schema(),
            ];
        }, $this->toolClasses);
    }

    public function execute(string $name, array $payload): mixed
    {
        foreach ($this->toolClasses as $toolClass) {
            /** @var Tool $tool */
            $tool = $this->container->make($toolClass);

            if ($tool->name() === $name) {
                return $tool->execute($payload);
            }
        }

        throw new ToolExecutionException("Tool [{$name}] is not registered.");
    }
}
