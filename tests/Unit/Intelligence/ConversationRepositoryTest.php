<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\DTOs\ChatMessage;
use App\Domains\Intelligence\Enums\ChatRole;
use App\Domains\Intelligence\Repositories\ConversationRepository;
use App\Models\User;
use Database\Seeders\AdministratorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_repository_persists_conversations_and_messages(): void
    {
        $this->seed(AdministratorSeeder::class);

        $user = User::factory()->create();
        $repository = app(ConversationRepository::class);

        $conversation = $repository->create([
            'user_id' => $user->id,
            'title' => 'Repository test',
            'provider' => 'ollama',
            'model' => 'runtime-placeholder',
            'status' => 'active',
            'metadata' => ['seeded' => true],
        ]);

        $message = $repository->addMessage($conversation, new ChatMessage(
            role: ChatRole::Assistant,
            content: 'Runtime scaffolding persisted.',
        ));

        $this->assertNotNull($repository->find($conversation->id));
        $this->assertSame($conversation->id, $message->conversation_id);
    }
}
