<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gateway_requests', function (Blueprint $table): void {
            $table->uuid('connected_erp_id')->nullable()->after('organization_id');
            $table->string('correlation_id')->nullable()->after('status')->index();
            $table->string('provider')->nullable()->after('provider_profile_id')->index();
            $table->string('model')->nullable()->after('provider')->index();
            $table->boolean('verification_passed')->nullable()->after('model')->index();
            $table->decimal('cost', 12, 6)->nullable()->after('latency_ms');
            $table->json('request_payload')->nullable()->after('cost');
            $table->json('response_payload')->nullable()->after('request_payload');
            $table->json('verification_payload')->nullable()->after('response_payload');
            $table->json('tool_payload')->nullable()->after('verification_payload');
            $table->timestamp('completed_at')->nullable()->after('tool_payload');

            $table->foreign('connected_erp_id')->references('id')->on('connected_erps')->nullOnDelete();
        });

        Schema::table('ai_provider_profiles', function (Blueprint $table): void {
            $table->json('metadata')->nullable()->after('configuration');
            $table->timestamp('last_checked_at')->nullable()->after('metadata');
            $table->unsignedInteger('last_latency_ms')->nullable()->after('last_checked_at');
        });

        Schema::create('provider_health_checks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('provider_key')->index();
            $table->string('status')->index();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->unsignedInteger('available_models')->default(0);
            $table->json('payload')->nullable();
            $table->timestamp('checked_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_health_checks');

        Schema::table('ai_provider_profiles', function (Blueprint $table): void {
            $table->dropColumn(['metadata', 'last_checked_at', 'last_latency_ms']);
        });

        Schema::table('gateway_requests', function (Blueprint $table): void {
            $table->dropForeign(['connected_erp_id']);
            $table->dropColumn([
                'connected_erp_id',
                'correlation_id',
                'provider',
                'model',
                'verification_passed',
                'cost',
                'request_payload',
                'response_payload',
                'verification_payload',
                'tool_payload',
                'completed_at',
            ]);
        });
    }
};
