<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Tools\Security\SecretVaultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretVaultServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_secret_vault_encrypts_and_reveals_credentials(): void
    {
        $credential = app(SecretVaultService::class)->store(
            name: 'CRM API',
            slug: 'crm_api',
            type: 'api_key',
            payload: ['token' => 'super-secret'],
        );

        $revealed = app(SecretVaultService::class)->reveal($credential);

        $this->assertSame('super-secret', $revealed['token']);
        $this->assertDatabaseHas('tool_credentials', [
            'slug' => 'crm_api',
            'type' => 'api_key',
        ]);
    }
}
