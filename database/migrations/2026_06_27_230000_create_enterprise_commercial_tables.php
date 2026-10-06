<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createTableIfMissing('intelligence_packages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tier')->index();
            $table->text('description')->nullable();
            $table->string('support_level')->nullable();
            $table->string('sla_level')->nullable();
            $table->json('deployment_mode_eligibility')->nullable();
            $table->json('enabled_agent_capabilities')->nullable();
            $table->unsignedInteger('monthly_execution_limit')->nullable();
            $table->unsignedBigInteger('token_usage_limit')->nullable();
            $table->unsignedInteger('knowledge_storage_limit_mb')->nullable();
            $table->unsignedInteger('team_member_limit')->nullable();
            $table->unsignedInteger('approval_workflow_limit')->nullable();
            $table->unsignedInteger('connector_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('package_features', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_package_id')->constrained('intelligence_packages')->cascadeOnDelete();
            $table->string('feature_key');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('package_limits', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_package_id')->constrained('intelligence_packages')->cascadeOnDelete();
            $table->string('limit_key');
            $table->decimal('limit_value', 14, 2)->default(0);
            $table->string('unit')->nullable();
            $table->boolean('is_hard_limit')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('package_entitlements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_package_id')->constrained('intelligence_packages')->cascadeOnDelete();
            $table->string('entitlement_key');
            $table->string('label');
            $table->string('status')->default('enabled');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('package_module_accesses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_package_id')->constrained('intelligence_packages')->cascadeOnDelete();
            $table->string('module_key');
            $table->string('access_level')->default('full');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('package_pricing_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_package_id')->constrained('intelligence_packages')->cascadeOnDelete();
            $table->string('billing_frequency')->default('monthly');
            $table->string('currency', 8)->default('ZAR');
            $table->decimal('base_price', 14, 2)->default(0);
            $table->string('overage_policy')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('package_upgrade_paths', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('from_package_id')->constrained('intelligence_packages')->cascadeOnDelete();
            $table->foreignUuid('to_package_id')->constrained('intelligence_packages')->cascadeOnDelete();
            $table->string('path_label');
            $table->json('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('intelligence_tenants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('industry')->nullable();
            $table->string('status')->default('draft')->index();
            $table->string('deployment_mode')->nullable();
            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_email')->nullable();
            $table->json('package_snapshot')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('tenant_workspaces', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->string('workspace_key');
            $table->string('name');
            $table->string('status')->default('provisioning');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('tenant_provisioning_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->foreignUuid('intelligence_package_id')->nullable()->constrained('intelligence_packages')->nullOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->string('deployment_mode')->nullable();
            $table->timestamp('go_live_target_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('checklist_snapshot')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('tenant_provisioning_checklists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_provisioning_request_id')->constrained('tenant_provisioning_requests', indexName: 'tenant_checklist_request_fk')->cascadeOnDelete();
            $table->string('task_key');
            $table->string('label');
            $table->string('status')->default('pending');
            $table->string('owner_name')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        if (! collect(Schema::getForeignKeys('tenant_provisioning_checklists'))->contains(
            fn (array $key): bool => $key['columns'] === ['tenant_provisioning_request_id']
        )) {
            Schema::table('tenant_provisioning_checklists', function (Blueprint $table): void {
                $table->foreign('tenant_provisioning_request_id', 'tenant_checklist_request_fk')
                    ->references('id')->on('tenant_provisioning_requests')->cascadeOnDelete();
            });
        }
        $this->createTableIfMissing('tenant_environments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->string('environment_name');
            $table->string('environment_type')->nullable();
            $table->string('status')->default('ready');
            $table->string('endpoint')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('tenant_domains', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->string('domain');
            $table->string('domain_type')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('tenant_branding_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->unique()->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->string('brand_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('tenant_security_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->unique()->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->string('policy_level')->nullable();
            $table->boolean('mfa_required')->default(false);
            $table->json('ip_allow_list')->nullable();
            $table->string('data_encryption_level')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('tenant_data_residency_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->unique()->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->string('jurisdiction')->nullable();
            $table->string('retention_policy')->nullable();
            $table->string('backup_region')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('tenant_deployment_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->unique()->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->string('deployment_mode')->nullable();
            $table->string('environment_strategy')->nullable();
            $table->string('release_channel')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('billing_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->string('account_name');
            $table->string('billing_email')->nullable();
            $table->string('currency', 8)->default('ZAR');
            $table->string('status')->default('ready');
            $table->json('address_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('billing_contacts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('billing_account_id')->constrained('billing_accounts')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('role')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('payment_instructions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('billing_account_id')->constrained('billing_accounts')->cascadeOnDelete();
            $table->string('instruction_type');
            $table->string('reference')->nullable();
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('intelligence_subscriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->foreignUuid('intelligence_package_id')->nullable()->constrained('intelligence_packages')->nullOnDelete();
            $table->foreignUuid('billing_account_id')->nullable()->constrained('billing_accounts')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->decimal('monthly_price', 14, 2)->default(0);
            $table->string('currency', 8)->default('ZAR');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('subscription_invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_subscription_id')->constrained('intelligence_subscriptions')->cascadeOnDelete();
            $table->foreignUuid('billing_account_id')->nullable()->constrained('billing_accounts')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('status')->default('draft');
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->string('currency', 8)->default('ZAR');
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('subscription_invoice_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_invoice_id')->constrained('subscription_invoices')->cascadeOnDelete();
            $table->string('line_type');
            $table->string('description');
            $table->decimal('quantity', 14, 2)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('usage_meters', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_subscription_id')->constrained('intelligence_subscriptions')->cascadeOnDelete();
            $table->string('meter_key');
            $table->string('label');
            $table->decimal('usage_total', 14, 2)->default(0);
            $table->decimal('usage_limit', 14, 2)->default(0);
            $table->decimal('warning_threshold', 8, 2)->default(80);
            $table->boolean('hard_limit')->default(false);
            $table->timestamp('period_started_at')->nullable();
            $table->timestamp('period_ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('usage_ledger_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('usage_meter_id')->constrained('usage_meters')->cascadeOnDelete();
            $table->string('entry_type');
            $table->decimal('quantity', 14, 2)->default(0);
            $table->string('status')->default('recorded');
            $table->timestamp('recorded_at')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('usage_quotas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_subscription_id')->constrained('intelligence_subscriptions')->cascadeOnDelete();
            $table->string('quota_key');
            $table->decimal('quota_limit', 14, 2)->default(0);
            $table->decimal('consumed', 14, 2)->default(0);
            $table->boolean('warning_state')->default(false);
            $table->string('enforcement_mode')->default('soft');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('usage_overages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_subscription_id')->constrained('intelligence_subscriptions')->cascadeOnDelete();
            $table->foreignUuid('usage_meter_id')->nullable()->constrained('usage_meters')->nullOnDelete();
            $table->string('overage_key');
            $table->decimal('quantity', 14, 2)->default(0);
            $table->string('status')->default('warning');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('intelligence_proposals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->constrained('intelligence_tenants')->cascadeOnDelete();
            $table->foreignUuid('intelligence_package_id')->nullable()->constrained('intelligence_packages')->nullOnDelete();
            $table->string('stage')->default('draft');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('currency', 8)->default('ZAR');
            $table->decimal('estimated_monthly_value', 14, 2)->default(0);
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('proposal_package_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_proposal_id')->constrained('intelligence_proposals')->cascadeOnDelete();
            $table->string('line_label');
            $table->decimal('quantity', 14, 2)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('proposal_deployment_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_proposal_id')->constrained('intelligence_proposals')->cascadeOnDelete();
            $table->string('line_label');
            $table->string('deployment_mode')->nullable();
            $table->decimal('line_total', 14, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('proposal_service_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_proposal_id')->constrained('intelligence_proposals')->cascadeOnDelete();
            $table->string('line_label');
            $table->string('service_type')->nullable();
            $table->decimal('quantity', 14, 2)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('proposal_assumptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_proposal_id')->constrained('intelligence_proposals')->cascadeOnDelete();
            $table->text('assumption');
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('proposal_risks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_proposal_id')->constrained('intelligence_proposals')->cascadeOnDelete();
            $table->text('risk');
            $table->string('severity')->default('medium');
            $table->text('mitigation')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('proposal_approvals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_proposal_id')->constrained('intelligence_proposals')->cascadeOnDelete();
            $table->string('approver_name');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('commercial_handovers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_proposal_id')->nullable()->constrained('intelligence_proposals')->nullOnDelete();
            $table->foreignUuid('intelligence_tenant_id')->nullable()->constrained('intelligence_tenants')->nullOnDelete();
            $table->string('handover_status')->default('ready');
            $table->json('handover_payload')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('deployment_runbooks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->nullable()->constrained('intelligence_tenants')->nullOnDelete();
            $table->foreignUuid('intelligence_proposal_id')->nullable()->constrained('intelligence_proposals')->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->string('deployment_mode')->nullable();
            $table->json('runbook_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('deployment_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_runbook_id')->constrained('deployment_runbooks')->cascadeOnDelete();
            $table->string('step_key');
            $table->string('title');
            $table->string('status')->default('pending');
            $table->unsignedInteger('sequence')->default(1);
            $table->string('owner_name')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('deployment_evidences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_runbook_id')->nullable()->constrained('deployment_runbooks')->nullOnDelete();
            $table->foreignUuid('deployment_step_id')->nullable()->constrained('deployment_steps')->nullOnDelete();
            $table->string('evidence_type');
            $table->string('label');
            $table->string('path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('support_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->nullable()->constrained('intelligence_tenants')->nullOnDelete();
            $table->foreignUuid('intelligence_package_id')->nullable()->constrained('intelligence_packages')->nullOnDelete();
            $table->string('support_level')->nullable();
            $table->string('sla_name')->nullable();
            $table->string('coverage_hours')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('support_tickets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->nullable()->constrained('intelligence_tenants')->nullOnDelete();
            $table->foreignUuid('support_plan_id')->nullable()->constrained('support_plans')->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('new')->index();
            $table->string('severity')->default('medium');
            $table->string('assigned_to')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('ticket_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('support_slas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('support_plan_id')->nullable()->constrained('support_plans')->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('response_time_minutes')->default(60);
            $table->unsignedInteger('resolution_time_minutes')->default(480);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('support_escalations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('support_ticket_id')->nullable()->constrained('support_tickets')->nullOnDelete();
            $table->string('escalation_level');
            $table->string('status')->default('pending');
            $table->timestamp('escalated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('maintenance_windows', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->nullable()->constrained('intelligence_tenants')->nullOnDelete();
            $table->string('title');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->createTableIfMissing('release_readiness_checks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('intelligence_tenant_id')->nullable()->constrained('intelligence_tenants')->nullOnDelete();
            $table->foreignUuid('deployment_runbook_id')->nullable()->constrained('deployment_runbooks')->nullOnDelete();
            $table->string('status')->default('review_required');
            $table->decimal('score', 8, 2)->default(0);
            $table->json('check_payload')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    private function createTableIfMissing(string $name, callable $definition): void
    {
        if (! Schema::hasTable($name)) {
            Schema::create($name, $definition);
        }
    }

    public function down(): void
    {
        foreach ([
            'release_readiness_checks',
            'maintenance_windows',
            'support_escalations',
            'support_slas',
            'support_tickets',
            'support_plans',
            'deployment_evidences',
            'deployment_steps',
            'deployment_runbooks',
            'commercial_handovers',
            'proposal_approvals',
            'proposal_risks',
            'proposal_assumptions',
            'proposal_service_lines',
            'proposal_deployment_lines',
            'proposal_package_lines',
            'intelligence_proposals',
            'usage_overages',
            'usage_quotas',
            'usage_ledger_entries',
            'usage_meters',
            'subscription_invoice_lines',
            'subscription_invoices',
            'intelligence_subscriptions',
            'payment_instructions',
            'billing_contacts',
            'billing_accounts',
            'tenant_deployment_profiles',
            'tenant_data_residency_profiles',
            'tenant_security_profiles',
            'tenant_branding_profiles',
            'tenant_domains',
            'tenant_environments',
            'tenant_provisioning_checklists',
            'tenant_provisioning_requests',
            'tenant_workspaces',
            'intelligence_tenants',
            'package_upgrade_paths',
            'package_pricing_rules',
            'package_module_accesses',
            'package_entitlements',
            'package_limits',
            'package_features',
            'intelligence_packages',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
