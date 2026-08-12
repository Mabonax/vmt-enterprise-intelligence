<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Actions;

use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Services\ReleaseReadinessService;

class RunReleaseReadinessCheckAction
{
    public function __construct(private readonly ReleaseReadinessService $service) {}

    public function execute(IntelligenceTenant $tenant): array
    {
        return $this->service->check($tenant);
    }
}