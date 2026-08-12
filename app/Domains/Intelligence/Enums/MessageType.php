<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Enums;

enum MessageType: string
{
    case Text = 'text';
    case ToolCall = 'tool_call';
    case ToolResult = 'tool_result';
    case SystemInstruction = 'system_instruction';
}
