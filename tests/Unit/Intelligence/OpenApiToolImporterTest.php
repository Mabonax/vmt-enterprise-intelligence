<?php

declare(strict_types=1);

namespace Tests\Unit\Intelligence;

use App\Domains\Intelligence\Services\OpenApiToolImporter;
use Tests\TestCase;

class OpenApiToolImporterTest extends TestCase
{
    public function test_openapi_importer_generates_connector_ready_tool_manifests(): void
    {
        $tools = app(OpenApiToolImporter::class)->import([
            'info' => ['version' => '2026.06'],
            'servers' => [['url' => 'https://example.test']],
            'paths' => [
                '/customers' => [
                    'get' => [
                        'summary' => 'Search customers',
                        'responses' => ['200' => ['description' => 'ok']],
                    ],
                ],
            ],
        ]);

        $this->assertCount(1, $tools);
        $this->assertSame('get_customers', $tools[0]->slug);
        $this->assertSame('openapi', $tools[0]->connector);
        $this->assertSame('https://example.test/customers', $tools[0]->metadata['endpoint']);
    }
}
