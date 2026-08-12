<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table): void {
            if (! Schema::hasColumn('agents', 'agent_role_key')) {
                $table->string('agent_role_key')->nullable()->after('slug')->index();
            }

            if (! Schema::hasColumn('agents', 'reasoning_style')) {
                $table->string('reasoning_style')->default('balanced')->after('default_model')->index();
            }

            if (! Schema::hasColumn('agents', 'risk_tolerance')) {
                $table->string('risk_tolerance')->default('moderate')->after('reasoning_style')->index();
            }

            if (! Schema::hasColumn('agents', 'verification_strategy')) {
                $table->string('verification_strategy')->default('reviewer')->after('risk_tolerance');
            }

            if (! Schema::hasColumn('agents', 'memory_scope')) {
                $table->string('memory_scope')->default('shared')->after('verification_strategy')->index();
            }

            if (! Schema::hasColumn('agents', 'delegation_enabled')) {
                $table->boolean('delegation_enabled')->default(true)->after('memory_enabled');
            }

            if (! Schema::hasColumn('agents', 'approval_required')) {
                $table->boolean('approval_required')->default(false)->after('delegation_enabled');
            }
        });

        Schema::create('agent_roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('role_key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('default_prompt')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('preferred_tools')->nullable();
            $table->string('reasoning_style')->default('balanced');
            $table->string('risk_tolerance')->default('moderate');
            $table->string('verification_strategy')->default('reviewer');
            $table->string('memory_scope')->default('shared');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_id')->unique();
            $table->uuid('agent_role_id')->nullable()->index();
            $table->string('identity')->nullable();
            $table->text('prompt')->nullable();
            $table->json('preferred_tools')->nullable();
            $table->json('capabilities')->nullable();
            $table->string('reasoning_style')->default('balanced');
            $table->string('risk_tolerance')->default('moderate');
            $table->string('verification_strategy')->default('reviewer');
            $table->string('memory_scope')->default('shared');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->cascadeOnDelete();
            $table->foreign('agent_role_id')->references('id')->on('agent_roles')->nullOnDelete();
        });

        Schema::create('agent_capabilities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_id')->nullable()->index();
            $table->uuid('agent_role_id')->nullable()->index();
            $table->string('capability_key')->index();
            $table->string('capability_type')->default('core')->index();
            $table->decimal('confidence_weight', 5, 2)->default(0.75);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->cascadeOnDelete();
            $table->foreign('agent_role_id')->references('id')->on('agent_roles')->cascadeOnDelete();
        });

        Schema::create('agent_memory', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_id')->nullable()->index();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->string('memory_key')->index();
            $table->string('memory_type')->default('shared')->index();
            $table->string('scope')->default('shared')->index();
            $table->text('title')->nullable();
            $table->longText('content');
            $table->json('references')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('agent_contexts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_id')->nullable()->index();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->string('context_key')->index();
            $table->json('context_payload');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_conversations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->string('topic');
            $table->string('status')->default('active')->index();
            $table->json('participants')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_conversation_id')->index();
            $table->uuid('sender_agent_id')->nullable()->index();
            $table->uuid('recipient_agent_id')->nullable()->index();
            $table->string('message_type')->default('request')->index();
            $table->string('status')->default('sent')->index();
            $table->text('subject')->nullable();
            $table->longText('content');
            $table->json('references')->nullable();
            $table->json('tool_output')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_conversation_id')->references('id')->on('agent_conversations')->cascadeOnDelete();
        });

        Schema::create('agent_tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('parent_task_id')->nullable()->index();
            $table->uuid('owner_agent_id')->nullable()->index();
            $table->string('title');
            $table->text('objective')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('priority')->default('normal')->index();
            $table->string('task_type')->default('execution')->index();
            $table->unsignedInteger('sequence')->default(1);
            $table->json('dependencies')->nullable();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('parent_task_id')->references('id')->on('agent_tasks')->nullOnDelete();
        });

        Schema::create('agent_task_assignments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_task_id')->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->uuid('agent_role_id')->nullable()->index();
            $table->string('assignment_type')->default('primary')->index();
            $table->string('status')->default('assigned')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_task_id')->references('id')->on('agent_tasks')->cascadeOnDelete();
            $table->foreign('agent_role_id')->references('id')->on('agent_roles')->nullOnDelete();
        });

        Schema::create('agent_workflows', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('lead_agent_id')->nullable()->index();
            $table->string('name');
            $table->string('status')->default('pending')->index();
            $table->string('execution_mode')->default('sequential')->index();
            $table->json('branching_rules')->nullable();
            $table->json('approval_gates')->nullable();
            $table->json('rollback_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_workflow_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_workflow_id')->index();
            $table->uuid('agent_task_id')->nullable()->index();
            $table->string('name');
            $table->unsignedInteger('sequence')->default(1);
            $table->string('status')->default('pending')->index();
            $table->string('step_kind')->default('analysis')->index();
            $table->boolean('is_parallel')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->json('condition_payload')->nullable();
            $table->json('result_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_workflow_id')->references('id')->on('agent_workflows')->cascadeOnDelete();
            $table->foreign('agent_task_id')->references('id')->on('agent_tasks')->nullOnDelete();
        });

        Schema::create('agent_reasoning_chains', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->string('goal');
            $table->json('plan')->nullable();
            $table->json('actions')->nullable();
            $table->json('tool_usage')->nullable();
            $table->json('knowledge_references')->nullable();
            $table->json('verification')->nullable();
            $table->decimal('confidence', 5, 2)->default(0.75);
            $table->string('decision')->nullable();
            $table->text('result_summary')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_thought_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_reasoning_chain_id')->index();
            $table->string('snapshot_type')->default('plan')->index();
            $table->text('summary');
            $table->json('evidence')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_reasoning_chain_id')->references('id')->on('agent_reasoning_chains')->cascadeOnDelete();
        });

        if (! Schema::hasTable('agent_delegations')) {
            Schema::create('agent_delegations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('agent_session_id')->nullable()->index();
                $table->uuid('source_agent_id')->nullable()->index();
                $table->uuid('target_agent_id')->nullable()->index();
                $table->uuid('agent_task_id')->nullable()->index();
                $table->string('status')->default('pending')->index();
                $table->text('objective');
                $table->json('handover_payload')->nullable();
                $table->json('result_payload')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('agent_delegations', function (Blueprint $table): void {
                if (! Schema::hasColumn('agent_delegations', 'agent_session_id')) {
                    $table->uuid('agent_session_id')->nullable()->after('id')->index();
                }

                if (! Schema::hasColumn('agent_delegations', 'agent_task_id')) {
                    $table->uuid('agent_task_id')->nullable()->after('target_agent_id')->index();
                }

                if (! Schema::hasColumn('agent_delegations', 'handover_payload')) {
                    $table->json('handover_payload')->nullable()->after('objective');
                }

                if (! Schema::hasColumn('agent_delegations', 'metadata')) {
                    $table->json('metadata')->nullable()->after('result_payload');
                }
            });
        }

        Schema::create('agent_collaborations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('source_agent_id')->nullable()->index();
            $table->uuid('target_agent_id')->nullable()->index();
            $table->string('collaboration_type')->default('handover')->index();
            $table->json('payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_decisions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->string('decision_key')->index();
            $table->text('why')->nullable();
            $table->json('alternatives')->nullable();
            $table->text('chosen_path')->nullable();
            $table->decimal('confidence', 5, 2)->default(0.75);
            $table->json('risks')->nullable();
            $table->json('references')->nullable();
            $table->json('tool_evidence')->nullable();
            $table->json('knowledge_evidence')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_approvals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('agent_workflow_id')->nullable()->index();
            $table->string('approval_type')->default('human')->index();
            $table->string('requested_role')->nullable()->index();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->text('reason')->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_execution_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('agent_workflow_step_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->string('event')->index();
            $table->string('status')->default('recorded')->index();
            $table->text('message');
            $table->json('payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_execution_metrics', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->string('metric_key')->index();
            $table->decimal('metric_value', 14, 4)->default(0);
            $table->string('unit')->default('count');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_checkpoints', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->string('checkpoint_type')->default('progress')->index();
            $table->string('status')->default('recorded')->index();
            $table->json('snapshot_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_state', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_id')->nullable()->index();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->string('current_status')->default('idle')->index();
            $table->string('current_task_id')->nullable()->index();
            $table->json('working_memory')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_team_members', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->uuid('agent_role_id')->nullable()->index();
            $table->string('member_name');
            $table->string('member_type')->default('specialist')->index();
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('order_column')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('agent_role_id')->references('id')->on('agent_roles')->nullOnDelete();
        });

        Schema::create('agent_queue', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->string('queue_name')->default('default')->index();
            $table->string('status')->default('queued')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('available_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->index();
            $table->string('status')->default('pending')->index();
            $table->text('message');
            $table->json('payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_feedback', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_learning', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->string('learning_type')->default('execution')->index();
            $table->text('summary')->nullable();
            $table->json('best_practices')->nullable();
            $table->json('workflow_templates')->nullable();
            $table->json('decision_templates')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_cost_tracking', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->uuid('agent_id')->nullable()->index();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('estimated_cost', 12, 4)->default(0);
            $table->json('tool_costs')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_quality_scores', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->decimal('completeness_score', 5, 2)->default(0);
            $table->decimal('evidence_score', 5, 2)->default(0);
            $table->decimal('policy_score', 5, 2)->default(0);
            $table->decimal('verification_score', 5, 2)->default(0);
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_verifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->decimal('confidence', 5, 2)->default(0.75);
            $table->json('missing_evidence')->nullable();
            $table->json('tool_coverage')->nullable();
            $table->json('knowledge_coverage')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_replay', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->json('timeline')->nullable();
            $table->json('reasoning_graph')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_summaries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_session_id')->nullable()->index();
            $table->string('summary_type')->default('final')->index();
            $table->text('summary');
            $table->json('highlights')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('agent_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_key')->unique();
            $table->string('title');
            $table->text('objective');
            $table->string('status')->default('pending')->index();
            $table->string('execution_mode')->default('queued')->index();
            $table->string('approval_role')->nullable()->index();
            $table->boolean('requires_human_approval')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('context_payload')->nullable();
            $table->json('result_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_sessions');
        Schema::dropIfExists('agent_summaries');
        Schema::dropIfExists('agent_replay');
        Schema::dropIfExists('agent_verifications');
        Schema::dropIfExists('agent_quality_scores');
        Schema::dropIfExists('agent_cost_tracking');
        Schema::dropIfExists('agent_learning');
        Schema::dropIfExists('agent_feedback');
        Schema::dropIfExists('agent_notifications');
        Schema::dropIfExists('agent_queue');
        Schema::dropIfExists('agent_team_members');
        Schema::dropIfExists('agent_state');
        Schema::dropIfExists('agent_checkpoints');
        Schema::dropIfExists('agent_execution_metrics');
        Schema::dropIfExists('agent_execution_logs');
        Schema::dropIfExists('agent_approvals');
        Schema::dropIfExists('agent_decisions');
        Schema::dropIfExists('agent_collaborations');
        Schema::dropIfExists('agent_delegations');
        Schema::dropIfExists('agent_thought_snapshots');
        Schema::dropIfExists('agent_reasoning_chains');
        Schema::dropIfExists('agent_workflow_steps');
        Schema::dropIfExists('agent_workflows');
        Schema::dropIfExists('agent_task_assignments');
        Schema::dropIfExists('agent_tasks');
        Schema::dropIfExists('agent_messages');
        Schema::dropIfExists('agent_conversations');
        Schema::dropIfExists('agent_contexts');
        Schema::dropIfExists('agent_memory');
        Schema::dropIfExists('agent_capabilities');
        Schema::dropIfExists('agent_profiles');
        Schema::dropIfExists('agent_roles');
    }
};
