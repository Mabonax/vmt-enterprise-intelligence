<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Validators;

class AllowedOriginValidator
{
    /**
     * @param array<int, mixed> $origins
     * @return list<string>
     */
    public function validate(array $origins): array
    {
        return collect($origins)
            ->filter(static fn ($origin): bool => is_string($origin) && filter_var($origin, FILTER_VALIDATE_URL) !== false)
            ->map(static fn (string $origin): string => rtrim($origin, '/'))
            ->unique()
            ->values()
            ->all();
    }
}
