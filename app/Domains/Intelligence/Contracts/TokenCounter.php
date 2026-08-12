<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

interface TokenCounter
{
    public function estimate(array|string $input): int;
}
