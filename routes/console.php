<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', static function () {
    echo Inspiring::quote().PHP_EOL;
})->purpose('Display an inspiring quote');

Schedule::job(new \App\Jobs\KnowledgeCleanupJob())->hourly();
Schedule::job(new \App\Jobs\KnowledgeHealthJob())->everyTwoHours();
Schedule::job(new \App\Jobs\LearningCycleJob())->everyThreeHours();
Schedule::job(new \App\Jobs\MemoryConsolidationJob())->daily();
Schedule::job(new \App\Jobs\ReindexKnowledgeJob())->dailyAt('01:00');
Schedule::job(new \App\Jobs\KnowledgeVerificationJob())->dailyAt('02:00');
