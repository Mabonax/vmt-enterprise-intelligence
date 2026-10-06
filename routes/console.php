<?php

use App\Domains\Intelligence\Gateway\Services\GatewayHealthService;
use App\Jobs\KnowledgeCleanupJob;
use App\Jobs\KnowledgeHealthJob;
use App\Jobs\KnowledgeVerificationJob;
use App\Jobs\LearningCycleJob;
use App\Jobs\MemoryConsolidationJob;
use App\Jobs\ReindexKnowledgeJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', static function () {
    echo Inspiring::quote().PHP_EOL;
})->purpose('Display an inspiring quote');

Artisan::command('gateway:readiness', function (): int {
    $status = app(GatewayHealthService::class)->status();

    echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

    return (bool) data_get($status, 'readiness.ready', false) ? 0 : 1;
})->purpose('Validate that the Enterprise AI Gateway can serve production local-AI traffic');

Schedule::job(new KnowledgeCleanupJob)->hourly();
Schedule::job(new KnowledgeHealthJob)->everyTwoHours();
Schedule::job(new LearningCycleJob)->everyThreeHours();
Schedule::job(new MemoryConsolidationJob)->daily();
Schedule::job(new ReindexKnowledgeJob)->dailyAt('01:00');
Schedule::job(new KnowledgeVerificationJob)->dailyAt('02:00');
