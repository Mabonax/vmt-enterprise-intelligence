<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\DTOs;

final class UsageMeterRecordData
{
    public function __construct(
        public readonly string $meterKey,
        public readonly float $quantity,
        public readonly array $context = [],
    ) {}
}