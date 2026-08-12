<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_console_dashboards', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('audience_type')->index();
            $table->json('layout_config')->nullable();
            $table->json('widget_config')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('admin_console_widgets', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('dashboard_id')->nullable()->constrained('admin_console_dashboards')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('widget_type')->index();
            $table->string('data_source');
            $table->unsignedInteger('refresh_interval_seconds')->nullable();
            $table->json('query_config')->nullable();
            $table->json('display_config')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('admin_console_alerts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('alert_type')->index();
            $table->string('severity')->index();
            $table->string('title');
            $table->text('message');
            $table->string('source_context')->nullable()->index();
            $table->string('source_type')->nullable()->index();
            $table->string('source_id')->nullable()->index();
            $table->string('status')->default('open')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_console_actions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('action_type')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('target_context')->nullable()->index();
            $table->string('target_type')->nullable()->index();
            $table->string('target_id')->nullable()->index();
            $table->string('status')->default('requested')->index();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_console_audit_events', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type')->index();
            $table->string('event_name')->index();
            $table->string('source_context')->nullable()->index();
            $table->string('target_type')->nullable()->index();
            $table->string('target_id')->nullable()->index();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });

        Schema::create('admin_console_health_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('snapshot_type')->index();
            $table->string('overall_status')->index();
            $table->decimal('score', 8, 2)->nullable();
            $table->json('signals')->nullable();
            $table->json('recommendations')->nullable();
            $table->timestamp('captured_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'admin_console_health_snapshots',
            'admin_console_audit_events',
            'admin_console_actions',
            'admin_console_alerts',
            'admin_console_widgets',
            'admin_console_dashboards',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
