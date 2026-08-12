<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Enums;

enum ConversationStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
