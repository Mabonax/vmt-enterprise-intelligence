<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Validators;

class AllowedIpValidator
{
    /**
     * @param array<int, mixed> $ips
     * @return list<string>
     */
    public function validate(array $ips): array
    {
        return collect($ips)
            ->filter(static fn ($ip): bool => is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false)
            ->unique()
            ->values()
            ->all();
    }
}
