<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Providers\Anthropic;

use App\Domains\Intelligence\Providers\AbstractStubProvider;

class AnthropicProvider extends AbstractStubProvider
{
    public function key(): string
    {
        return 'anthropic';
    }
}
