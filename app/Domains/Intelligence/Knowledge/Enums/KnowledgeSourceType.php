<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Enums;

enum KnowledgeSourceType: string
{
    case Document = 'document';
    case Conversation = 'conversation';
    case Workflow = 'workflow';
    case ExecutionTrace = 'execution_trace';
    case Verification = 'verification';
    case ToolOutput = 'tool_output';
    case Project = 'project';
    case ErpEntity = 'erp_entity';
}
