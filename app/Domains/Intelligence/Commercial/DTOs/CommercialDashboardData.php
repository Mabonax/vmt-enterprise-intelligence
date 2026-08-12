<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\DTOs;

final class CommercialDashboardData
{
    public function __construct(
        public readonly array $kpis,
        public readonly array $pipelines,
    ) {}

    public function toArray(): array
    {
        return [
            'kpis' => $this->kpis,
            'pipelines' => $this->pipelines,
        ];
    }
}