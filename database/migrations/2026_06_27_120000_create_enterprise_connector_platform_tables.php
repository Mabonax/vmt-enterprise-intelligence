<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('tool_packages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version');
            $table->string('publisher')->nullable();
            $table->string('status')->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('enterprise_tools', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('category_id')->nullable()->index();
            $table->uuid('package_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('connector_type')->default('laravel')->index();
            $table->string('handler_class')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('status')->default('active')->index();
            $table->string('publisher')->nullable();
            $table->string('manifest_source')->default('attribute');
            $table->json('input_schema')->nullable();
            $table->json('output_schema')->nullable();
            $table->json('permissions')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('tags')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('security_policy')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['slug', 'version']);

            $table->foreign('category_id')->references('id')->on('tool_categories')->nullOnDelete();
            $table->foreign('package_id')->references('id')->on('tool_packages')->nullOnDelete();
        });

        Schema::create('tool_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_tool_id')->index();
            $table->string('version');
            $table->boolean('is_current')->default(true)->index();
            $table->string('compatibility_range')->nullable();
            $table->json('manifest_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['enterprise_tool_id', 'version']);

            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
        });

        Schema::create('tool_dependencies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_tool_id')->index();
            $table->string('dependency_slug');
            $table->string('constraint')->default('*');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
        });

        Schema::create('tool_credentials', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->string('status')->default('active')->index();
            $table->longText('encrypted_payload');
            $table->timestamp('last_rotated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tool_permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_tool_id')->index();
            $table->string('permission_key')->nullable()->index();
            $table->string('role_name')->nullable()->index();
            $table->string('department')->nullable()->index();
            $table->string('workspace')->nullable()->index();
            $table->string('security_level')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
        });

        Schema::create('tool_health', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_tool_id')->unique();
            $table->boolean('availability')->default(true)->index();
            $table->unsignedInteger('latency_ms')->default(0);
            $table->unsignedInteger('timeouts')->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->unsignedInteger('average_duration_ms')->default(0);
            $table->decimal('success_rate', 6, 2)->default(0);
            $table->timestamp('last_execution_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->decimal('health_score', 6, 2)->default(100);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
        });

        Schema::create('tool_usage', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_tool_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('execution_trace_id')->nullable()->index();
            $table->boolean('success')->default(true)->index();
            $table->unsignedInteger('duration_ms')->default(0);
            $table->unsignedInteger('tokens')->default(0);
            $table->unsignedBigInteger('bandwidth_bytes')->default(0);
            $table->unsignedBigInteger('storage_bytes')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
            $table->foreign('execution_trace_id')->references('id')->on('execution_traces')->nullOnDelete();
        });

        Schema::create('tool_costs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_tool_id')->index();
            $table->uuid('execution_trace_id')->nullable()->index();
            $table->decimal('api_usage_cost', 12, 4)->default(0);
            $table->decimal('llm_token_cost', 12, 4)->default(0);
            $table->decimal('storage_cost', 12, 4)->default(0);
            $table->decimal('bandwidth_cost', 12, 4)->default(0);
            $table->decimal('estimated_cost', 12, 4)->default(0);
            $table->decimal('credits_consumed', 12, 4)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
            $table->foreign('execution_trace_id')->references('id')->on('execution_traces')->nullOnDelete();
        });

        Schema::create('enterprise_tool_executions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_tool_id')->index();
            $table->uuid('execution_trace_id')->nullable()->index();
            $table->uuid('parent_execution_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->string('version');
            $table->string('connector_type')->index();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->json('replay_payload')->nullable();
            $table->string('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
            $table->foreign('execution_trace_id')->references('id')->on('execution_traces')->nullOnDelete();
        });

        Schema::create('tool_execution_graph', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('execution_trace_id')->nullable()->index();
            $table->uuid('tool_execution_id')->index();
            $table->uuid('parent_tool_execution_id')->nullable()->index();
            $table->string('node_key')->index();
            $table->unsignedInteger('depth')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('execution_trace_id')->references('id')->on('execution_traces')->nullOnDelete();
            $table->foreign('tool_execution_id')->references('id')->on('enterprise_tool_executions')->cascadeOnDelete();
        });

        Schema::create('tool_test_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_tool_id')->index();
            $table->string('test_type')->index();
            $table->string('status')->default('pending')->index();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
        });

        Schema::create('connector_registrations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('driver');
            $table->string('status')->default('active')->index();
            $table->json('configuration')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('connector_health', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('connector_registration_id')->unique();
            $table->string('status')->default('healthy')->index();
            $table->unsignedInteger('latency_ms')->default(0);
            $table->timestamp('last_checked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('connector_registration_id')->references('id')->on('connector_registrations')->cascadeOnDelete();
        });

        Schema::create('marketplace_packages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tool_package_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('available')->index();
            $table->string('version');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tool_package_id')->references('id')->on('tool_packages')->nullOnDelete();
        });

        Schema::create('marketplace_installations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('marketplace_package_id')->index();
            $table->uuid('enterprise_tool_id')->index();
            $table->string('status')->default('installed')->index();
            $table->string('installed_version');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('marketplace_package_id')->references('id')->on('marketplace_packages')->cascadeOnDelete();
            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
        });

        Schema::create('sdk_exports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_tool_id')->index();
            $table->string('language')->index();
            $table->string('status')->default('pending')->index();
            $table->string('path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_tool_id')->references('id')->on('enterprise_tools')->cascadeOnDelete();
        });

        Schema::create('execution_streams', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tool_execution_id')->index();
            $table->string('status')->default('pending')->index();
            $table->text('message');
            $table->unsignedInteger('sequence')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('tool_execution_id')->references('id')->on('enterprise_tool_executions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('execution_streams');
        Schema::dropIfExists('sdk_exports');
        Schema::dropIfExists('marketplace_installations');
        Schema::dropIfExists('marketplace_packages');
        Schema::dropIfExists('connector_health');
        Schema::dropIfExists('connector_registrations');
        Schema::dropIfExists('tool_test_runs');
        Schema::dropIfExists('tool_execution_graph');
        Schema::dropIfExists('enterprise_tool_executions');
        Schema::dropIfExists('tool_costs');
        Schema::dropIfExists('tool_usage');
        Schema::dropIfExists('tool_health');
        Schema::dropIfExists('tool_permissions');
        Schema::dropIfExists('tool_credentials');
        Schema::dropIfExists('tool_dependencies');
        Schema::dropIfExists('tool_versions');
        Schema::dropIfExists('enterprise_tools');
        Schema::dropIfExists('tool_packages');
        Schema::dropIfExists('tool_categories');
    }
};
