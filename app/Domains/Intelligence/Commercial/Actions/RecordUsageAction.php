<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Actions;

use App\Domains\Intelligence\Commercial\DTOs\UsageMeterRecordData;
use App\Domains\Intelligence\Commercial\Services\UsageMeteringService;

class RecordUsageAction
{
    public function __construct(private readonly UsageMeteringService $metering) {}

    public function execute(string $subscriptionId, UsageMeterRecordData $data): array
    {
        return $this->metering->record($subscriptionId, $data);
    }
}