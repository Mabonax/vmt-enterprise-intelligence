<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class VerificationResultData
{
    /**
     * @param list<array<string, mixed>> $checks
     * @param list<string> $missingInformation
     */
    public function __construct(
        public bool $passed,
        public float $confidenceScore,
        public array $checks = [],
        public array $missingInformation = [],
        public array $metadata = [],
    ) {}
}
