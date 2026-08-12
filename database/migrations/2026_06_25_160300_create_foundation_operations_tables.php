<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prompt_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('scope');
            $table->string('name');
            $table->unsignedInteger('version')->default(1);
            $table->string('approval_status')->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });

        Schema::create('memory_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->uuid('agent_id')->nullable();
            $table->string('memory_scope');
            $table->string('session_key')->nullable();
            $table->json('content');
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });

        Schema::create('agent_definitions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->json('capabilities')->nullable();
            $table->json('policy_metadata')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });

        Schema::create('tool_registrations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('connected_erp_id')->nullable();
            $table->string('tool_name');
            $table->string('status')->default('registered');
            $table->json('schema')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('connected_erp_id')->references('id')->on('connected_erps')->nullOnDelete();
        });

        Schema::create('workflow_blueprints', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->json('definition')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });

        Schema::create('billing_subscriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('plan');
            $table->string('status')->default('draft');
            $table->json('rate_card')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });

        Schema::create('billing_usage_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('gateway_request_id')->nullable();
            $table->string('usage_type');
            $table->decimal('quantity', 14, 4)->default(0);
            $table->decimal('unit_price', 14, 4)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('gateway_request_id')->references('id')->on('gateway_requests')->nullOnDelete();
        });

        Schema::create('monitoring_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('metric_group');
            $table->string('metric_key');
            $table->json('payload')->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();
        });

        Schema::create('audit_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_entries');
        Schema::dropIfExists('monitoring_snapshots');
        Schema::dropIfExists('billing_usage_records');
        Schema::dropIfExists('billing_subscriptions');
        Schema::dropIfExists('workflow_blueprints');
        Schema::dropIfExists('tool_registrations');
        Schema::dropIfExists('agent_definitions');
        Schema::dropIfExists('memory_entries');
        Schema::dropIfExists('prompt_templates');
    }
};
