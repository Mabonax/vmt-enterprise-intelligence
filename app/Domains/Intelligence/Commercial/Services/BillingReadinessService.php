<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\BillingAccount;

class BillingReadinessService
{
    public function summary(): array
    {
        return [
            'accounts' => BillingAccount::query()->count(),
            'ready_accounts' => BillingAccount::query()->where('status', 'ready')->count(),
            'missing_contacts' => BillingAccount::query()->doesntHave('contacts')->count(),
        ];
    }
}