<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Policies;

use App\Models\User;

class AdminConsolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('administrator')
            || $user->can('intelligence.admin_console.view')
            || $user->can('intelligence.admin_console.manage');
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function manage(User $user): bool
    {
        return $user->hasRole('administrator')
            || $user->can('intelligence.admin_console.manage');
    }

    public function manageAlerts(User $user): bool
    {
        return $this->manage($user)
            || $user->can('intelligence.admin_console.alerts.manage');
    }

    public function manageActions(User $user): bool
    {
        return $this->manage($user)
            || $user->can('intelligence.admin_console.actions.manage');
    }

    public function viewAudit(User $user): bool
    {
        return $this->manage($user)
            || $user->can('intelligence.admin_console.audit.view');
    }

    public function viewHealth(User $user): bool
    {
        return $this->manage($user)
            || $user->can('intelligence.admin_console.health.view');
    }

    public function viewReadiness(User $user): bool
    {
        return $this->manage($user)
            || $user->can('intelligence.admin_console.readiness.view');
    }
}
