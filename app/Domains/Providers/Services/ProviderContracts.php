<?php

declare(strict_types=1);

namespace App\Domains\Providers\Services;

use App\Domains\Gateway\Services\ChatGatewayInterface;
use App\Domains\Gateway\Services\CompletionGatewayInterface;
use App\Domains\Gateway\Services\ConversationMemoryGatewayInterface;
use App\Domains\Gateway\Services\EmbeddingGatewayInterface;
use App\Domains\Gateway\Services\OcrGatewayInterface;
use App\Domains\Gateway\Services\SpeechGatewayInterface;
use App\Domains\Gateway\Services\StreamingGatewayInterface;
use App\Domains\Gateway\Services\ToolCallingGatewayInterface;
use App\Domains\Gateway\Services\TranslationGatewayInterface;
use App\Domains\Gateway\Services\VisionGatewayInterface;

/**
 * Base contract that every AI provider adapter must expose.
 */
interface AiProviderInterface extends
    ChatGatewayInterface,
    CompletionGatewayInterface,
    EmbeddingGatewayInterface,
    VisionGatewayInterface,
    OcrGatewayInterface,
    SpeechGatewayInterface,
    TranslationGatewayInterface,
    ToolCallingGatewayInterface,
    StreamingGatewayInterface,
    ConversationMemoryGatewayInterface
{
    public function key(): string;
}

/**
 * Prepared provider contract for Llama-family integrations.
 */
interface LlamaProviderInterface extends AiProviderInterface {}

/**
 * Prepared provider contract for OpenAI integrations.
 */
interface OpenAiProviderInterface extends AiProviderInterface {}

/**
 * Prepared provider contract for Claude integrations.
 */
interface ClaudeProviderInterface extends AiProviderInterface {}

/**
 * Prepared provider contract for Gemini integrations.
 */
interface GeminiProviderInterface extends AiProviderInterface {}

/**
 * Prepared provider contract for Mistral integrations.
 */
interface MistralProviderInterface extends AiProviderInterface {}

/**
 * Prepared provider contract for DeepSeek integrations.
 */
interface DeepSeekProviderInterface extends AiProviderInterface {}

/**
 * Prepared provider contract for Azure OpenAI integrations.
 */
interface AzureOpenAiProviderInterface extends AiProviderInterface {}

/**
 * Prepared provider contract for Ollama integrations.
 */
interface OllamaProviderInterface extends AiProviderInterface {}
