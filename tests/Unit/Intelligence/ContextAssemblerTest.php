<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Services\ContextAssembler;
use Tests\TestCase;

class ContextAssemblerTest extends TestCase
{
    public function test_context_assembler_packages_manual_and_pinned_context(): void
    {
        $context = app(ContextAssembler::class)->assemble(
            systemPrompt: 'Runtime',
            manualContext: ['scope' => 'manual'],
            pinnedContext: ['scope' => 'pinned'],
            userPrompt: 'Continue.',
        );

        $this->assertSame('Runtime', $context->systemPrompt);
        $this->assertSame('manual', $context->manualContext['scope']);
        $this->assertSame('pinned', $context->pinnedContext['scope']);
        $this->assertContains('manual', $context->runtimeContext['selected']);
        $this->assertSame('Continue.', $context->userPrompt);
    }
}
