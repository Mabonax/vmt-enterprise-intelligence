<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Contracts;

use App\Domains\Intelligence\Contracts\IntelligenceTool;

interface EnterpriseToolInterface extends IntelligenceTool
{
    public function version(): string;

    public function connector(): string;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array;
}
