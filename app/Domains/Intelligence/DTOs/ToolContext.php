<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Models\Conversation;
use App\Models\User;

final readonly class ToolContext
{
    public function __construct(
        public ?User $user,
        public ?Conversation $conversation = null,
        public ?Agent $agent = null,
        public array $memory = [],
        public array $metadata = [],
    ) {}
}
