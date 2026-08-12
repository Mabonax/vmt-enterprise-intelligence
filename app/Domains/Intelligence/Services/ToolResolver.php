<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\IntelligenceTool;
use App\Domains\Intelligence\Exceptions\ToolExecutionException;

class ToolResolver
{
    public function __construct(
        private readonly \Illuminate\Contracts\Container\Container $container,
        private readonly array $toolClasses,
    ) {}

    public function resolve(string $slug): IntelligenceTool
    {
        foreach ($this->toolClasses as $toolClass) {
            /** @var IntelligenceTool $tool */
            $tool = $this->container->make($toolClass);

            if ($tool->slug() === $slug || $tool->name() === $slug) {
                return $tool;
            }
        }

        throw new ToolExecutionException("Tool [{$slug}] is not registered.");
    }
}
