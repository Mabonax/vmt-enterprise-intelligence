<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('execution_plans', function (Blueprint $table): void {
            $table->string('completion_state')->default('open')->after('estimated_complexity');
            $table->json('retry_strategy')->nullable()->after('dependencies');
        });

        Schema::table('execution_plan_steps', function (Blueprint $table): void {
            $table->string('step_kind')->default('task')->after('tool_slug');
            $table->json('required_tools')->nullable()->after('dependencies');
            $table->json('verification_requirements')->nullable()->after('required_tools');
        });

        Schema::table('workflow_executions', function (Blueprint $table): void {
            $table->json('execution_history')->nullable()->after('approval_checkpoints');
        });

        Schema::table('tool_execution_logs', function (Blueprint $table): void {
            $table->json('authorization_payload')->nullable()->after('error_message');
        });

        Schema::table('execution_traces', function (Blueprint $table): void {
            $table->json('trace_payload')->nullable()->after('verification_payload');
            $table->json('delegation_payload')->nullable()->after('trace_payload');
        });
    }

    public function down(): void
    {
        Schema::table('execution_traces', function (Blueprint $table): void {
            $table->dropColumn(['trace_payload', 'delegation_payload']);
        });

        Schema::table('tool_execution_logs', function (Blueprint $table): void {
            $table->dropColumn(['authorization_payload']);
        });

        Schema::table('workflow_executions', function (Blueprint $table): void {
            $table->dropColumn(['execution_history']);
        });

        Schema::table('execution_plan_steps', function (Blueprint $table): void {
            $table->dropColumn(['step_kind', 'required_tools', 'verification_requirements']);
        });

        Schema::table('execution_plans', function (Blueprint $table): void {
            $table->dropColumn(['completion_state', 'retry_strategy']);
        });
    }
};
