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
        Schema::create('organizations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->string('status')->default('draft');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('connected_erps', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name');
            $table->string('system_key');
            $table->string('base_url')->nullable();
            $table->string('status')->default('draft');
            $table->json('allowed_models')->nullable();
            $table->json('permissions')->nullable();
            $table->json('rate_limits')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });

        Schema::create('erp_api_keys', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('connected_erp_id');
            $table->string('name');
            $table->string('key_hash');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('connected_erp_id')->references('id')->on('connected_erps')->cascadeOnDelete();
        });

        Schema::create('connection_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('connected_erp_id');
            $table->string('direction');
            $table->string('status')->default('queued');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->string('request_identifier')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();

            $table->foreign('connected_erp_id')->references('id')->on('connected_erps')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connection_logs');
        Schema::dropIfExists('erp_api_keys');
        Schema::dropIfExists('connected_erps');
        Schema::dropIfExists('organizations');
    }
};
