<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Actions;

use App\Domains\Intelligence\Commercial\Models\TenantProvisioningRequest;

class SubmitProvisioningRequestAction
{
    public function execute(TenantProvisioningRequest $request): TenantProvisioningRequest
    {
        $request->forceFill(['status' => 'submitted'])->save();

        return $request->fresh();
    }
}