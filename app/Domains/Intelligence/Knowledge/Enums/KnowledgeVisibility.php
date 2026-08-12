<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Enums;

enum KnowledgeVisibility: string
{
    case Private = 'private';
    case Organization = 'organization';
    case Workspace = 'workspace';
    case Restricted = 'restricted';
}
