<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enterprise_missions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mission_key')->unique();
            $table->string('title');
            $table->text('objective_summary');
            $table->string('priority')->default('medium')->index();
            $table->string('status')->default('draft')->index();
            $table->string('owner_name')->nullable();
            $table->decimal('budget_amount', 14, 2)->nullable();
            $table->string('budget_currency', 12)->default('USD');
            $table->timestamp('deadline_at')->nullable()->index();
            $table->timestamp('estimated_completion_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->decimal('health_score', 5, 2)->default(0);
            $table->decimal('risk_score', 5, 2)->default(0);
            $table->json('mission_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('mission_objectives', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('objective_type')->default('initiative')->index();
            $table->string('priority')->default('medium')->index();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('sequence')->default(1);
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_phases', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('name');
            $table->string('phase_type')->default('initiative')->index();
            $table->string('status')->default('pending')->index();
            $table->unsignedInteger('sequence')->default(1);
            $table->string('owner_team')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_executions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('execution_key')->unique();
            $table->string('status')->default('queued')->index();
            $table->string('execution_mode')->default('autonomous')->index();
            $table->string('assigned_team')->nullable();
            $table->json('assigned_agents')->nullable();
            $table->json('snapshot_payload')->nullable();
            $table->json('result_payload')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_checkpoint_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_milestones', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('title');
            $table->string('status')->default('pending')->index();
            $table->timestamp('target_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_checkpoints', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->uuid('mission_execution_id')->nullable()->index();
            $table->string('checkpoint_type')->default('progress')->index();
            $table->string('status')->default('recorded')->index();
            $table->json('snapshot_payload')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
            $table->foreign('mission_execution_id')->references('id')->on('mission_executions')->nullOnDelete();
        });

        Schema::create('mission_dependencies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('dependency_type')->default('sequence')->index();
            $table->string('source_reference');
            $table->string('target_reference');
            $table->string('status')->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_outcomes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('outcome_type')->default('delivery')->index();
            $table->string('status')->default('pending')->index();
            $table->text('summary')->nullable();
            $table->decimal('verification_score', 5, 2)->default(0);
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->json('payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_risks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('title');
            $table->string('risk_type')->default('delivery')->index();
            $table->string('severity')->default('medium')->index();
            $table->string('probability')->default('possible')->index();
            $table->decimal('impact_score', 5, 2)->default(0);
            $table->string('status')->default('open')->index();
            $table->text('mitigation_plan')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_metrics', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('metric_key')->index();
            $table->decimal('metric_value', 14, 4)->default(0);
            $table->string('unit')->default('count');
            $table->timestamp('recorded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_plan_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->string('status')->default('draft')->index();
            $table->string('complexity')->default('medium')->index();
            $table->decimal('estimated_duration_hours', 12, 2)->default(0);
            $table->decimal('estimated_financial_cost', 14, 2)->default(0);
            $table->decimal('estimated_token_cost', 14, 2)->default(0);
            $table->json('tool_requirements')->nullable();
            $table->json('knowledge_requirements')->nullable();
            $table->json('required_agent_skills')->nullable();
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->decimal('risk_score', 5, 2)->default(0);
            $table->json('compliance_requirements')->nullable();
            $table->json('dependency_map')->nullable();
            $table->json('critical_path')->nullable();
            $table->json('alternative_plans')->nullable();
            $table->json('plan_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
            $table->unique(['enterprise_mission_id', 'version_number']);
        });

        Schema::create('operations_agent_catalog', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider')->nullable()->index();
            $table->string('preferred_model')->nullable();
            $table->string('status')->default('active')->index();
            $table->decimal('historical_success_rate', 5, 2)->default(0);
            $table->decimal('trust_score', 5, 2)->default(0);
            $table->unsignedInteger('average_latency_ms')->default(0);
            $table->decimal('average_cost', 14, 2)->default(0);
            $table->decimal('current_workload', 5, 2)->default(0);
            $table->json('permissions')->nullable();
            $table->json('tool_ownership')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('operations_agent_capabilities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_catalog_id')->index();
            $table->string('capability_key')->index();
            $table->string('capability_type')->default('execution')->index();
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_catalog_id')->references('id')->on('operations_agent_catalog')->cascadeOnDelete();
        });

        Schema::create('operations_agent_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_catalog_id')->index();
            $table->string('version');
            $table->string('status')->default('active')->index();
            $table->timestamp('released_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_catalog_id')->references('id')->on('operations_agent_catalog')->cascadeOnDelete();
        });

        Schema::create('operations_agent_providers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('provider_key')->unique();
            $table->string('name');
            $table->string('status')->default('available')->index();
            $table->unsignedInteger('latency_ms')->default(0);
            $table->decimal('health_score', 5, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('operations_agent_skills', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_catalog_id')->index();
            $table->string('skill_key')->index();
            $table->string('skill_level')->default('intermediate')->index();
            $table->string('language')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_catalog_id')->references('id')->on('operations_agent_catalog')->cascadeOnDelete();
        });

        Schema::create('operations_agent_availability', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_catalog_id')->index();
            $table->string('availability_status')->default('available')->index();
            $table->decimal('capacity_percentage', 5, 2)->default(100);
            $table->timestamp('available_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_catalog_id')->references('id')->on('operations_agent_catalog')->cascadeOnDelete();
        });

        Schema::create('enterprise_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->nullable()->index();
            $table->string('event_type')->index();
            $table->string('event_key')->unique();
            $table->string('subject_type')->nullable()->index();
            $table->string('subject_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamp('replayed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->nullOnDelete();
        });

        Schema::create('mission_policies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('policy_type')->index();
            $table->string('status')->default('approved')->index();
            $table->decimal('evaluation_score', 5, 2)->default(0);
            $table->json('violations')->nullable();
            $table->json('recommendations')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_compliance_reviews', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('framework')->index();
            $table->string('status')->default('compliant')->index();
            $table->decimal('compliance_score', 5, 2)->default(0);
            $table->json('findings')->nullable();
            $table->json('remediation_actions')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_approvals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('approval_type')->default('sequential')->index();
            $table->unsignedInteger('sequence_order')->default(1);
            $table->string('requested_role')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->string('delegated_to')->nullable();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->json('signature_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_predictions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('prediction_type')->index();
            $table->string('prediction_window')->default('current')->index();
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->decimal('predicted_value', 12, 2)->default(0);
            $table->json('recommendations')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('scenario_simulations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('scenario_type')->index();
            $table->string('scenario_label');
            $table->string('status')->default('simulated')->index();
            $table->json('predicted_outcome')->nullable();
            $table->json('impact_summary')->nullable();
            $table->json('simulation_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_decision_records', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('decision_key')->index();
            $table->json('context')->nullable();
            $table->json('alternatives')->nullable();
            $table->longText('reasoning')->nullable();
            $table->json('evidence')->nullable();
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->text('chosen_option')->nullable();
            $table->json('approvals')->nullable();
            $table->text('outcome')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->timestamp('review_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('mission_learning_cycles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('enterprise_mission_id')->index();
            $table->string('learning_type')->index();
            $table->string('status')->default('captured')->index();
            $table->text('improvement_summary')->nullable();
            $table->json('improvement_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('enterprise_mission_id')->references('id')->on('enterprise_missions')->cascadeOnDelete();
        });

        Schema::create('enterprise_kpi_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('snapshot_key')->unique();
            $table->timestamp('recorded_at')->nullable()->index();
            $table->json('kpi_payload');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_kpi_snapshots');
        Schema::dropIfExists('mission_learning_cycles');
        Schema::dropIfExists('mission_decision_records');
        Schema::dropIfExists('scenario_simulations');
        Schema::dropIfExists('mission_predictions');
        Schema::dropIfExists('mission_approvals');
        Schema::dropIfExists('mission_compliance_reviews');
        Schema::dropIfExists('mission_policies');
        Schema::dropIfExists('enterprise_events');
        Schema::dropIfExists('operations_agent_availability');
        Schema::dropIfExists('operations_agent_skills');
        Schema::dropIfExists('operations_agent_providers');
        Schema::dropIfExists('operations_agent_versions');
        Schema::dropIfExists('operations_agent_capabilities');
        Schema::dropIfExists('operations_agent_catalog');
        Schema::dropIfExists('mission_plan_versions');
        Schema::dropIfExists('mission_metrics');
        Schema::dropIfExists('mission_risks');
        Schema::dropIfExists('mission_outcomes');
        Schema::dropIfExists('mission_dependencies');
        Schema::dropIfExists('mission_checkpoints');
        Schema::dropIfExists('mission_milestones');
        Schema::dropIfExists('mission_executions');
        Schema::dropIfExists('mission_phases');
        Schema::dropIfExists('mission_objectives');
        Schema::dropIfExists('enterprise_missions');
    }
};
