<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\DTOs\ContextWindow;
use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Exceptions\ContextOverflowException;
use App\Domains\Intelligence\Services\TokenManager;
use Tests\TestCase;

class TokenManagerTest extends TestCase
{
    public function test_token_manager_can_trim_history(): void
    {
        $manager = app(TokenManager::class);

        $history = [
            new ChatMessage(role: ChatRole::User, content: str_repeat('old ', 40)),
            new ChatMessage(role: ChatRole::Assistant, content: str_repeat('new ', 10)),
        ];

        $trimmed = $manager->trimHistory($history, new ContextWindow(maxTokens: 20, reservedOutputTokens: 4));

        $this->assertCount(1, $trimmed);
        $this->assertSame(str_repeat('new ', 10), $trimmed[0]->content);
    }

    public function test_token_manager_throws_when_context_exceeds_window(): void
    {
        $this->expectException(ContextOverflowException::class);

        app(TokenManager::class)->preventContextOverflow(
            new ContextWindow(maxTokens: 5, reservedOutputTokens: 1),
            str_repeat('token ', 20),
        );
    }
}
