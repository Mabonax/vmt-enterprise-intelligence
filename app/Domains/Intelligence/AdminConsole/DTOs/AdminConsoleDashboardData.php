<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\DTOs;

class AdminConsoleDashboardData
{
    public function __construct(
        public readonly array $dashboard,
        public readonly array $metrics,
        public readonly array $widgets,
        public readonly array $alerts,
        public readonly array $actions,
        public readonly array $health,
        public readonly array $readiness,
    ) {}

    public function toArray(): array
    {
        return [
            'dashboard' => $this->dashboard,
            'metrics' => $this->metrics,
            'widgets' => $this->widgets,
            'alerts' => $this->alerts,
            'actions' => $this->actions,
            'health' => $this->health,
            'readiness' => $this->readiness,
        ];
    }
}
