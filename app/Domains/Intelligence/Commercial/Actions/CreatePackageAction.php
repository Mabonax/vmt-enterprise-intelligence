<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Actions;

use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;

class CreatePackageAction
{
    public function execute(array $attributes): IntelligencePackage
    {
        return IntelligencePackage::query()->updateOrCreate(
            ['slug' => $attributes['slug']],
            $attributes,
        );
    }
}