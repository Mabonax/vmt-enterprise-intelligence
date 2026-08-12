<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('organization_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index();
            $table->string('visibility')->default('private')->index();
            $table->string('default_provider')->nullable();
            $table->string('default_model')->nullable();
            $table->text('description')->nullable();
            $table->text('purpose')->nullable();
            $table->longText('system_instructions')->nullable();
            $table->decimal('temperature', 4, 2)->default(0.2);
            $table->unsignedInteger('max_tokens')->nullable();
            $table->boolean('memory_enabled')->default(true);
            $table->unsignedInteger('conversation_limit')->nullable();
            $table->json('allowed_tools')->nullable();
            $table->json('allowed_knowledge_sources')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });

        Schema::table('prompt_templates', function (Blueprint $table): void {
            $table->foreignId('owner_user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('slug')->nullable()->after('name')->index();
            $table->string('category')->default('general')->after('slug')->index();
            $table->string('status')->default('draft')->after('approval_status')->index();
            $table->boolean('is_default')->default(false)->after('status');
            $table->text('description')->nullable()->after('is_default');
            $table->longText('system_prompt')->nullable()->after('description');
            $table->longText('developer_prompt')->nullable()->after('system_prompt');
            $table->longText('user_prompt_template')->nullable()->after('developer_prompt');
            $table->json('variables_schema')->nullable()->after('user_prompt_template');
            $table->json('output_schema')->nullable()->after('variables_schema');
            $table->softDeletes();
            $table->unique(['slug', 'version']);
        });

        Schema::create('semantic_memories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('organization_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->uuid('conversation_id')->nullable()->index();
            $table->uuid('conversation_message_id')->nullable()->index();
            $table->string('subject_type')->index();
            $table->string('subject_id')->index();
            $table->string('memory_type')->index();
            $table->string('visibility')->default('private')->index();
            $table->text('content');
            $table->unsignedTinyInteger('importance_score')->default(50);
            $table->decimal('confidence_score', 5, 2)->default(0.50);
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
            $table->foreign('conversation_id')->references('id')->on('conversations')->nullOnDelete();
            $table->foreign('conversation_message_id')->references('id')->on('conversation_messages')->nullOnDelete();
        });

        Schema::create('ai_tools', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('general')->index();
            $table->string('status')->default('active')->index();
            $table->string('handler_class')->unique();
            $table->string('permission_key')->nullable()->index();
            $table->boolean('requires_approval')->default(false);
            $table->unsignedInteger('timeout_seconds')->default(10);
            $table->text('description')->nullable();
            $table->json('input_schema')->nullable();
            $table->json('output_schema')->nullable();
            $table->json('permissions')->nullable();
            $table->json('tags')->nullable();
            $table->string('version')->default('1.0.0');
            $table->string('provider')->default('internal');
            $table->boolean('deprecated')->default(false);
            $table->json('examples')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tool_execution_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('ai_tool_id')->nullable()->index();
            $table->uuid('conversation_id')->nullable()->index();
            $table->uuid('conversation_message_id')->nullable()->index();
            $table->uuid('execution_trace_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tool_name')->index();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->string('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ai_tool_id')->references('id')->on('ai_tools')->nullOnDelete();
            $table->foreign('conversation_id')->references('id')->on('conversations')->nullOnDelete();
            $table->foreign('conversation_message_id')->references('id')->on('conversation_messages')->nullOnDelete();
        });

        Schema::create('model_routing_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('provider')->index();
            $table->string('model')->index();
            $table->string('capability')->index();
            $table->unsignedInteger('priority')->default(100)->index();
            $table->unsignedInteger('max_context_tokens')->nullable();
            $table->string('cost_tier')->default('standard')->index();
            $table->boolean('enabled')->default(true)->index();
            $table->string('fallback_provider')->nullable();
            $table->string('fallback_model')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('execution_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->string('objective');
            $table->string('status')->default('draft')->index();
            $table->string('estimated_complexity')->default('medium');
            $table->unsignedTinyInteger('max_iterations')->default(5);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->json('required_tools')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->nullOnDelete();
            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
        });

        Schema::create('execution_plan_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('execution_plan_id')->index();
            $table->uuid('workflow_execution_id')->nullable()->index();
            $table->unsignedInteger('sequence');
            $table->string('title');
            $table->string('tool_slug')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->json('dependencies')->nullable();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->unsignedTinyInteger('retry_limit')->default(1);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('verification_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('execution_plan_id')->references('id')->on('execution_plans')->cascadeOnDelete();
        });

        Schema::create('workflow_executions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->string('name');
            $table->string('status')->default('pending')->index();
            $table->json('steps')->nullable();
            $table->json('rollback_payload')->nullable();
            $table->json('approval_checkpoints')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->nullOnDelete();
            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
        });

        Schema::create('agent_delegations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('execution_trace_id')->nullable()->index();
            $table->uuid('source_agent_id')->nullable()->index();
            $table->uuid('target_agent_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->string('objective');
            $table->json('shared_context')->nullable();
            $table->json('result_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('source_agent_id')->references('id')->on('agents')->nullOnDelete();
            $table->foreign('target_agent_id')->references('id')->on('agents')->nullOnDelete();
        });

        Schema::create('verification_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('execution_trace_id')->nullable()->index();
            $table->uuid('conversation_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->decimal('confidence_score', 5, 2)->default(0.00);
            $table->json('checks')->nullable();
            $table->json('missing_information')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->nullOnDelete();
        });

        Schema::create('background_tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('execution_trace_id')->nullable()->index();
            $table->uuid('conversation_id')->nullable()->index();
            $table->string('type')->index();
            $table->string('status')->default('pending')->index();
            $table->string('queue')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->json('payload')->nullable();
            $table->json('result_payload')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->nullOnDelete();
        });

        Schema::create('execution_traces', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->uuid('execution_plan_id')->nullable()->index();
            $table->uuid('verification_log_id')->nullable()->index();
            $table->string('provider')->nullable()->index();
            $table->string('model')->nullable()->index();
            $table->string('completion_reason')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedTinyInteger('iterations')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->json('plan_payload')->nullable();
            $table->json('step_payloads')->nullable();
            $table->json('memory_payload')->nullable();
            $table->json('verification_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->nullOnDelete();
            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
            $table->foreign('execution_plan_id')->references('id')->on('execution_plans')->nullOnDelete();
        });

        Schema::create('knowledge_references', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('execution_trace_id')->nullable()->index();
            $table->uuid('knowledge_source_id')->nullable()->index();
            $table->string('title');
            $table->unsignedTinyInteger('relevance_score')->default(50);
            $table->json('excerpt')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('knowledge_source_id')->references('id')->on('knowledge_sources')->nullOnDelete();
        });

        Schema::create('planner_metrics', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('execution_plan_id')->nullable()->index();
            $table->unsignedTinyInteger('step_count')->default(0);
            $table->unsignedTinyInteger('tool_count')->default(0);
            $table->unsignedTinyInteger('dependency_count')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('execution_plan_id')->references('id')->on('execution_plans')->nullOnDelete();
        });

        Schema::table('conversations', function (Blueprint $table): void {
            $table->uuid('agent_id')->nullable()->after('user_id');
            $table->uuid('prompt_template_id')->nullable()->after('agent_id');
            $table->unsignedSmallInteger('conversation_limit')->nullable()->after('status');
            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
            $table->foreign('prompt_template_id')->references('id')->on('prompt_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropForeign(['prompt_template_id']);
            $table->dropForeign(['agent_id']);
            $table->dropColumn(['agent_id', 'prompt_template_id', 'conversation_limit']);
        });

        Schema::dropIfExists('planner_metrics');
        Schema::dropIfExists('knowledge_references');
        Schema::dropIfExists('execution_traces');
        Schema::dropIfExists('background_tasks');
        Schema::dropIfExists('verification_logs');
        Schema::dropIfExists('agent_delegations');
        Schema::dropIfExists('workflow_executions');
        Schema::dropIfExists('execution_plan_steps');
        Schema::dropIfExists('execution_plans');
        Schema::dropIfExists('model_routing_rules');
        Schema::dropIfExists('tool_execution_logs');
        Schema::dropIfExists('ai_tools');
        Schema::dropIfExists('semantic_memories');
        Schema::dropIfExists('agents');

        Schema::table('prompt_templates', function (Blueprint $table): void {
            $table->dropUnique(['slug', 'version']);
            $table->dropForeign(['owner_user_id']);
            $table->dropColumn([
                'owner_user_id',
                'slug',
                'category',
                'status',
                'is_default',
                'description',
                'system_prompt',
                'developer_prompt',
                'user_prompt_template',
                'variables_schema',
                'output_schema',
                'deleted_at',
            ]);
        });
    }
};
