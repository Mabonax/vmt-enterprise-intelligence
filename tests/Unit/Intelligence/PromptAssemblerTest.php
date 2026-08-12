<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Services\ContextAssembler;
use App\Domains\Intelligence\Services\PromptAssembler;
use Tests\TestCase;

class PromptAssemblerTest extends TestCase
{
    public function test_prompt_assembler_builds_system_context_history_and_user_prompt(): void
    {
        $assembler = new PromptAssembler();

        $messages = $assembler->build(app(ContextAssembler::class)->assemble(
            systemPrompt: 'You are the runtime.',
            conversationHistory: [
                new ChatMessage(role: ChatRole::Assistant, content: 'History lives here.'),
            ],
            manualContext: ['clinic' => 'VMT'],
            pinnedContext: ['mode' => 'safe'],
            toolDefinitions: [['name' => 'search', 'description' => 'Searches', 'schema' => []]],
            userPrompt: 'Answer carefully.',
        ));

        $this->assertCount(7, $messages);
        $this->assertSame('system', $messages[0]['role']);
        $this->assertStringContainsString('Runtime context', $messages[3]['content']);
        $this->assertSame('Answer carefully.', $messages[6]['content']);
    }
}
