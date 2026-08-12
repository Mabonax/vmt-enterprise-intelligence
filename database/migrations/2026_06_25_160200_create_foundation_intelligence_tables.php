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
        Schema::create('ai_provider_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('provider_key')->unique();
            $table->string('display_name');
            $table->string('status')->default('inactive');
            $table->json('capabilities')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();
        });

        Schema::create('gateway_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->uuid('provider_profile_id')->nullable();
            $table->string('capability');
            $table->string('status')->default('draft');
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('provider_profile_id')->references('id')->on('ai_provider_profiles')->nullOnDelete();
        });

        Schema::create('model_catalog', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('provider_profile_id')->nullable();
            $table->string('model_key')->unique();
            $table->string('category');
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('provider_profile_id')->references('id')->on('ai_provider_profiles')->nullOnDelete();
        });

        Schema::create('document_collections', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('name');
            $table->string('visibility')->default('private');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });

        Schema::create('knowledge_sources', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('document_collection_id')->nullable();
            $table->string('source_type');
            $table->string('title');
            $table->string('status')->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('document_collection_id')->references('id')->on('document_collections')->nullOnDelete();
        });

        Schema::create('knowledge_chunks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('knowledge_source_id');
            $table->uuid('model_catalog_id')->nullable();
            $table->unsignedInteger('position');
            $table->json('chunk_metadata')->nullable();
            $table->json('embedding_metadata')->nullable();
            $table->timestamps();

            $table->foreign('knowledge_source_id')->references('id')->on('knowledge_sources')->cascadeOnDelete();
            $table->foreign('model_catalog_id')->references('id')->on('model_catalog')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_chunks');
        Schema::dropIfExists('knowledge_sources');
        Schema::dropIfExists('document_collections');
        Schema::dropIfExists('model_catalog');
        Schema::dropIfExists('gateway_requests');
        Schema::dropIfExists('ai_provider_profiles');
    }
};
