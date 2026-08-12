<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_tenants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });

        Schema::create('gateway_clients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gateway_tenant_id')->index();
            $table->uuid('organization_id')->nullable()->index();
            $table->uuid('connected_erp_id')->nullable()->index();
            $table->string('client_type')->default('erp')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->string('environment')->default('production')->index();
            $table->unsignedInteger('key_version')->default(1);
            $table->json('allowed_origins')->nullable();
            $table->json('allowed_ips')->nullable();
            $table->unsignedInteger('rate_limit_per_minute')->nullable();
            $table->unsignedInteger('rate_limit_per_hour')->nullable();
            $table->unsignedInteger('rate_limit_per_day')->nullable();
            $table->unsignedBigInteger('daily_quota')->nullable();
            $table->unsignedInteger('concurrent_requests')->nullable();
            $table->unsignedInteger('burst_limit')->nullable();
            $table->json('enabled_providers')->nullable();
            $table->json('enabled_models')->nullable();
            $table->json('enabled_capabilities')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('gateway_tenant_id')->references('id')->on('gateway_tenants')->cascadeOnDelete();
            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('connected_erp_id')->references('id')->on('connected_erps')->nullOnDelete();
        });

        Schema::create('gateway_api_credentials', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gateway_client_id')->index();
            $table->string('auth_method')->default('api_key')->index();
            $table->string('key_identifier')->unique();
            $table->string('key_hash');
            $table->string('secret_hash')->nullable();
            $table->text('secret_ciphertext')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('active')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('gateway_client_id')->references('id')->on('gateway_clients')->cascadeOnDelete();
        });

        Schema::create('gateway_scopes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gateway_tenant_id')->index();
            $table->uuid('gateway_client_id')->nullable()->index();
            $table->string('scope')->index();
            $table->string('status')->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['gateway_tenant_id', 'gateway_client_id', 'scope'], 'gateway_scopes_unique_assignment');
            $table->foreign('gateway_tenant_id')->references('id')->on('gateway_tenants')->cascadeOnDelete();
            $table->foreign('gateway_client_id')->references('id')->on('gateway_clients')->cascadeOnDelete();
        });

        Schema::create('gateway_policies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gateway_tenant_id')->index();
            $table->string('name');
            $table->string('policy_type')->default('provider')->index();
            $table->string('status')->default('active')->index();
            $table->json('provider_rules')->nullable();
            $table->json('model_rules')->nullable();
            $table->json('capability_rules')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('gateway_tenant_id')->references('id')->on('gateway_tenants')->cascadeOnDelete();
        });

        Schema::create('gateway_rate_limits', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gateway_tenant_id')->index();
            $table->uuid('gateway_client_id')->nullable()->index();
            $table->string('window')->index();
            $table->unsignedInteger('max_requests')->nullable();
            $table->unsignedInteger('max_tokens')->nullable();
            $table->unsignedInteger('burst_limit')->nullable();
            $table->unsignedInteger('concurrent_limit')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('gateway_tenant_id')->references('id')->on('gateway_tenants')->cascadeOnDelete();
            $table->foreign('gateway_client_id')->references('id')->on('gateway_clients')->cascadeOnDelete();
        });

        Schema::create('gateway_usage', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gateway_tenant_id')->nullable()->index();
            $table->uuid('gateway_client_id')->nullable()->index();
            $table->uuid('gateway_request_id')->nullable()->index();
            $table->string('correlation_id')->nullable()->index();
            $table->string('capability')->nullable()->index();
            $table->string('provider')->nullable()->index();
            $table->string('model')->nullable()->index();
            $table->unsignedInteger('request_count')->default(1);
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('cost', 12, 6)->default(0);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->timestamp('measured_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('gateway_tenant_id')->references('id')->on('gateway_tenants')->nullOnDelete();
            $table->foreign('gateway_client_id')->references('id')->on('gateway_clients')->nullOnDelete();
            $table->foreign('gateway_request_id')->references('id')->on('gateway_requests')->nullOnDelete();
        });

        Schema::create('gateway_security_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gateway_tenant_id')->nullable()->index();
            $table->uuid('gateway_client_id')->nullable()->index();
            $table->string('event_type')->index();
            $table->string('severity')->default('info')->index();
            $table->string('auth_method')->nullable()->index();
            $table->string('endpoint')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('correlation_id')->nullable()->index();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('headers')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->foreign('gateway_tenant_id')->references('id')->on('gateway_tenants')->nullOnDelete();
            $table->foreign('gateway_client_id')->references('id')->on('gateway_clients')->nullOnDelete();
        });

        Schema::create('gateway_nonces', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gateway_client_id')->index();
            $table->string('nonce');
            $table->string('request_hash');
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->unique(['gateway_client_id', 'nonce']);
            $table->foreign('gateway_client_id')->references('id')->on('gateway_clients')->cascadeOnDelete();
        });

        Schema::create('gateway_quotas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('gateway_tenant_id')->index();
            $table->uuid('gateway_client_id')->nullable()->index();
            $table->string('quota_type')->index();
            $table->unsignedBigInteger('limit_value');
            $table->unsignedBigInteger('consumed_value')->default(0);
            $table->timestamp('window_starts_at')->index();
            $table->timestamp('window_ends_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('gateway_tenant_id')->references('id')->on('gateway_tenants')->cascadeOnDelete();
            $table->foreign('gateway_client_id')->references('id')->on('gateway_clients')->cascadeOnDelete();
        });

        Schema::table('gateway_requests', function (Blueprint $table): void {
            $table->uuid('gateway_tenant_id')->nullable()->after('organization_id');
            $table->uuid('gateway_client_id')->nullable()->after('gateway_tenant_id');
            $table->string('auth_method')->nullable()->after('connected_erp_id')->index();
            $table->json('scopes')->nullable()->after('tool_payload');
            $table->string('failure_reason')->nullable()->after('scopes');
            $table->ipAddress('ip_address')->nullable()->after('failure_reason');

            $table->foreign('gateway_tenant_id')->references('id')->on('gateway_tenants')->nullOnDelete();
            $table->foreign('gateway_client_id')->references('id')->on('gateway_clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gateway_requests', function (Blueprint $table): void {
            $table->dropForeign(['gateway_tenant_id']);
            $table->dropForeign(['gateway_client_id']);
            $table->dropColumn(['gateway_tenant_id', 'gateway_client_id', 'auth_method', 'scopes', 'failure_reason', 'ip_address']);
        });

        Schema::dropIfExists('gateway_quotas');
        Schema::dropIfExists('gateway_nonces');
        Schema::dropIfExists('gateway_security_events');
        Schema::dropIfExists('gateway_usage');
        Schema::dropIfExists('gateway_rate_limits');
        Schema::dropIfExists('gateway_policies');
        Schema::dropIfExists('gateway_scopes');
        Schema::dropIfExists('gateway_api_credentials');
        Schema::dropIfExists('gateway_clients');
        Schema::dropIfExists('gateway_tenants');
    }
};
