<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Enums;

enum ProviderType: string
{
    case OpenAI = 'openai';
    case Ollama = 'ollama';
    case Anthropic = 'anthropic';
    case Gemini = 'gemini';
    case LMStudio = 'lmstudio';
}
