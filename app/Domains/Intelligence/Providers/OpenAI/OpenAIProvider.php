<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Providers\OpenAI;

use App\Domains\Intelligence\Providers\AbstractStubProvider;

class OpenAIProvider extends AbstractStubProvider
{
    public function key(): string
    {
        return 'openai';
    }
}
