<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Providers\LMStudio;

use App\Domains\Intelligence\Providers\AbstractStubProvider;

class LMStudioProvider extends AbstractStubProvider
{
    public function key(): string
    {
        return 'lmstudio';
    }
}
