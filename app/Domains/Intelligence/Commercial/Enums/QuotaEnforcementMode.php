<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Enums;

enum QuotaEnforcementMode: string
{
    case Soft = 'soft';
    case Hard = 'hard';
}