<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Models\PromptTemplate;
use App\Domains\Intelligence\Services\PromptTemplateRenderer;
use Tests\TestCase;

class PromptTemplateRendererTest extends TestCase
{
    public function test_renderer_interpolates_prompt_variables(): void
    {
        $template = new PromptTemplate([
            'system_prompt' => 'System for {{organization.name}}',
            'developer_prompt' => 'Developer sees {{capability}}',
            'user_prompt_template' => 'Hello {{user.name}}',
        ]);

        $rendered = app(PromptTemplateRenderer::class)->render($template, [
            'organization' => ['name' => 'VMT'],
            'capability' => 'reasoning',
            'user' => ['name' => 'John'],
        ]);

        $this->assertSame('System for VMT', $rendered['system_prompt']);
        $this->assertSame('Developer sees reasoning', $rendered['developer_prompt']);
        $this->assertSame('Hello John', $rendered['user_prompt']);
    }
}
