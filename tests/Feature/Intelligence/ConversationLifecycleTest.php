<?php

declare(strict_types=1);

namespace Tests\Feature\Intelligence;

use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\DTOs\UsageStatistics;
use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Services\ConversationManager;
use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_manager_can_create_update_and_archive_conversations(): void
    {
        $this->seed(AdministratorSeeder::class);

        $user = User::factory()->create();
        $manager = app(ConversationManager::class);

        $conversation = $manager->createConversation(
            userId: $user->id,
            title: 'Provider-neutral runtime',
            provider: 'ollama',
            model: 'runtime-placeholder',
            metadata: ['source' => 'test'],
        );

        $message = $manager->addMessage($conversation, new ChatMessage(
            role: ChatRole::User,
            content: 'Create a runtime abstraction.',
            usage: new UsageStatistics(inputTokens: 12, outputTokens: 0, totalTokens: 12),
        ));

        $conversation = $manager->renameConversation($conversation, 'Runtime abstraction');
        $manager->editMessage($message, 'Create a provider-neutral runtime abstraction.');
        $conversation = $manager->archiveConversation($conversation);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'title' => 'Runtime abstraction',
            'status' => 'archived',
        ]);

        $this->assertDatabaseHas('conversation_messages', [
            'id' => $message->id,
            'content' => 'Create a provider-neutral runtime abstraction.',
        ]);

        $this->assertDatabaseHas('provider_usage', [
            'conversation_id' => $conversation->id,
            'provider' => 'ollama',
            'model' => 'runtime-placeholder',
        ]);
    }
}
