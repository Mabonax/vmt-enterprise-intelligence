<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use Tests\TestCase;

class ConfigurationLoadingTest extends TestCase
{
    public function test_intelligence_configuration_loads_expected_defaults(): void
    {
        $this->assertSame('ollama', config('intelligence.default_provider'));
        $this->assertSame('runtime-placeholder', config('intelligence.default_model'));
        $this->assertTrue(config('intelligence.tool_execution.enabled'));
        $this->assertSame(16000, config('intelligence.token_limits.max_context'));
    }
}
