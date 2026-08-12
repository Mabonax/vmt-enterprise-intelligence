<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Tools\DTOs\ToolManifestData;
use InvalidArgumentException;

class ToolValidator
{
    public function validate(ToolManifestData $manifest): void
    {
        if ($manifest->slug === '' || ! preg_match('/^[a-z0-9_]+$/', $manifest->slug)) {
            throw new InvalidArgumentException('Tool slug must be snake_case.');
        }

        if ($manifest->version === '') {
            throw new InvalidArgumentException('Tool version is required.');
        }
    }
}
