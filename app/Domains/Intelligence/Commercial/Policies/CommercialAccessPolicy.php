<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Policies;

use App\Models\User;

class CommercialAccessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function manage(User $user): bool
    {
        return $user !== null;
    }
}