<?php

declare(strict_types=1);

namespace App\Support\Auth;

enum SystemRole: string
{
    case Administrator = 'administrator';
    case OrganizationAdministrator = 'organization-administrator';
    case Developer = 'developer';
    case Viewer = 'viewer';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $role): string => $role->value,
            self::cases(),
        );
    }
}
