<?php

declare(strict_types=1);

namespace App\Domains\Gateway\Services;

/**
 * Defines the contract for conversational AI requests.
 */
interface ChatGatewayInterface
{
    public function chat(array $payload): mixed;
}

/**
 * Defines the contract for text completion requests.
 */
interface CompletionGatewayInterface
{
    public function complete(array $payload): mixed;
}

/**
 * Defines the contract for vector embedding requests.
 */
interface EmbeddingGatewayInterface
{
    public function embed(array $payload): mixed;
}

/**
 * Defines the contract for vision-capable requests.
 */
interface VisionGatewayInterface
{
    public function analyze(array $payload): mixed;
}

/**
 * Defines the contract for OCR requests.
 */
interface OcrGatewayInterface
{
    public function extract(array $payload): mixed;
}

/**
 * Defines the contract for speech requests.
 */
interface SpeechGatewayInterface
{
    public function transcribe(array $payload): mixed;
}

/**
 * Defines the contract for translation requests.
 */
interface TranslationGatewayInterface
{
    public function translate(array $payload): mixed;
}

/**
 * Defines the contract for tool-calling requests.
 */
interface ToolCallingGatewayInterface
{
    public function callTools(array $payload): mixed;
}

/**
 * Defines the contract for streaming responses.
 */
interface StreamingGatewayInterface
{
    public function stream(array $payload): iterable;
}

/**
 * Defines the contract for conversation memory orchestration.
 */
interface ConversationMemoryGatewayInterface
{
    public function recall(array $payload): mixed;
}
