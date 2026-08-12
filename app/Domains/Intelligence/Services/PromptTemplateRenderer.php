<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\PromptTemplate;

class PromptTemplateRenderer
{
    public function render(PromptTemplate $template, array $variables = []): array
    {
        return [
            'system_prompt' => $this->interpolate((string) $template->system_prompt, $variables),
            'developer_prompt' => $this->interpolate((string) $template->developer_prompt, $variables),
            'user_prompt' => $this->interpolate((string) $template->user_prompt_template, $variables),
        ];
    }

    private function interpolate(string $template, array $variables): string
    {
        return (string) preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/', function (array $matches) use ($variables): string {
            $key = $matches[1];

            return (string) data_get($variables, $key, $matches[0]);
        }, $template);
    }
}
