<?php

use App\Domains\Intelligence\Gateway\Services\GatewayHealthService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', static function () {
    echo Inspiring::quote().PHP_EOL;
})->purpose('Display an inspiring quote');

Artisan::command('gateway:readiness', static function (): int {
    $status = app(GatewayHealthService::class)->status();

    echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

    return (bool) data_get($status, 'readiness.ready', false) ? 0 : 1;
})->purpose('Validate that the Enterprise AI Gateway can serve production local-AI traffic');

Schedule::job(new \App\Jobs\KnowledgeCleanupJob())->hourly();
Schedule::job(new \App\Jobs\KnowledgeHealthJob())->everyTwoHours();
Schedule::job(new \App\Jobs\LearningCycleJob())->everyThreeHours();
Schedule::job(new \App\Jobs\MemoryConsolidationJob())->daily();
Schedule::job(new \App\Jobs\ReindexKnowledgeJob())->dailyAt('01:00');
Schedule::job(new \App\Jobs\KnowledgeVerificationJob())->dailyAt('02:00');
