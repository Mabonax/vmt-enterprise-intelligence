<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Enums;

enum KnowledgeMemoryType: string
{
    case Conversation = 'conversation';
    case User = 'user';
    case Project = 'project';
    case Customer = 'customer';
    case Workflow = 'workflow';
    case Execution = 'execution';
    case Verification = 'verification';
    case Tool = 'tool';
    case Decision = 'decision';
    case Policy = 'policy';
    case Document = 'document';
}
