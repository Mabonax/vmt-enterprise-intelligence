<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Providers\Gemini;

use App\Domains\Intelligence\Providers\AbstractStubProvider;

class GeminiProvider extends AbstractStubProvider
{
    public function key(): string
    {
        return 'gemini';
    }
}
