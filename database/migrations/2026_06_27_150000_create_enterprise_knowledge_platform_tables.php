<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_collections', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('workspace')->default('intelligence')->index();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('knowledge_sources', function (Blueprint $table): void {
            if (! Schema::hasColumn('knowledge_sources', 'source_id')) {
                $table->string('source_id')->nullable()->after('source_type')->index();
            }

            if (! Schema::hasColumn('knowledge_sources', 'uri')) {
                $table->string('uri')->nullable()->after('title');
            }
        });

        Schema::create('knowledge_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('knowledge_collection_id')->nullable()->index();
            $table->uuid('knowledge_source_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->index();
            $table->string('source_type')->index();
            $table->string('mime_type')->nullable();
            $table->string('language')->nullable();
            $table->string('status')->default('queued')->index();
            $table->string('visibility')->default('organization')->index();
            $table->string('classification')->default('internal')->index();
            $table->longText('content');
            $table->text('summary')->nullable();
            $table->json('keywords')->nullable();
            $table->decimal('quality_score', 5, 2)->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->string('checksum')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('knowledge_collection_id')->references('id')->on('knowledge_collections')->nullOnDelete();
            $table->foreign('knowledge_source_id')->references('id')->on('knowledge_sources')->nullOnDelete();
        });

        Schema::table('knowledge_chunks', function (Blueprint $table): void {
            if (! Schema::hasColumn('knowledge_chunks', 'knowledge_document_id')) {
                $table->uuid('knowledge_document_id')->nullable()->after('knowledge_source_id')->index();
                $table->foreign('knowledge_document_id')->references('id')->on('knowledge_documents')->nullOnDelete();
            }

            if (! Schema::hasColumn('knowledge_chunks', 'content')) {
                $table->longText('content')->nullable()->after('position');
            }

            if (! Schema::hasColumn('knowledge_chunks', 'chunk_strategy')) {
                $table->string('chunk_strategy')->default('paragraph')->after('content')->index();
            }

            if (! Schema::hasColumn('knowledge_chunks', 'chunk_order')) {
                $table->unsignedInteger('chunk_order')->default(1)->after('chunk_strategy');
            }

            if (! Schema::hasColumn('knowledge_chunks', 'token_count')) {
                $table->unsignedInteger('token_count')->default(0)->after('chunk_order');
            }

            if (! Schema::hasColumn('knowledge_chunks', 'overlap')) {
                $table->unsignedInteger('overlap')->default(0)->after('token_count');
            }

            if (! Schema::hasColumn('knowledge_chunks', 'checksum')) {
                $table->string('checksum')->nullable()->after('overlap')->index();
            }

            if (! Schema::hasColumn('knowledge_chunks', 'metadata')) {
                $table->json('metadata')->nullable()->after('embedding_metadata');
            }
        });

        Schema::create('knowledge_embeddings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('knowledge_chunk_id')->index();
            $table->string('provider')->index();
            $table->string('model')->index();
            $table->string('status')->default('generated')->index();
            $table->json('vector')->nullable();
            $table->unsignedInteger('dimensions')->default(0);
            $table->string('checksum')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('knowledge_chunk_id')->references('id')->on('knowledge_chunks')->cascadeOnDelete();
        });

        Schema::create('knowledge_entities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('knowledge_document_id')->nullable()->index();
            $table->string('entity_type')->index();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->unsignedInteger('occurrences')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('knowledge_document_id')->references('id')->on('knowledge_documents')->nullOnDelete();
        });

        Schema::create('knowledge_relationships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('source_type')->index();
            $table->string('source_id')->index();
            $table->string('target_type')->index();
            $table->string('target_id')->index();
            $table->string('relationship')->index();
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_graph_nodes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('node_type')->index();
            $table->string('node_id')->index();
            $table->string('label');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['node_type', 'node_id']);
        });

        Schema::create('knowledge_graph_edges', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('source_node_id')->index();
            $table->uuid('target_node_id')->index();
            $table->string('relationship')->index();
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('source_node_id')->references('id')->on('knowledge_graph_nodes')->cascadeOnDelete();
            $table->foreign('target_node_id')->references('id')->on('knowledge_graph_nodes')->cascadeOnDelete();
        });

        Schema::create('knowledge_memories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('knowledge_document_id')->nullable()->index();
            $table->string('memory_type')->index();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('owner_type')->nullable()->index();
            $table->string('owner_id')->nullable()->index();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('visibility')->default('organization')->index();
            $table->string('classification')->default('internal')->index();
            $table->decimal('importance', 5, 2)->default(0.70);
            $table->decimal('confidence', 5, 2)->default(0.70);
            $table->timestamp('expiry_at')->nullable();
            $table->json('embedding')->nullable();
            $table->json('citations')->nullable();
            $table->json('relationships')->nullable();
            $table->json('created_from')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('knowledge_document_id')->references('id')->on('knowledge_documents')->nullOnDelete();
        });

        Schema::create('knowledge_tags', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('knowledge_document_id')->nullable()->index();
            $table->string('tag')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('knowledge_document_id')->references('id')->on('knowledge_documents')->nullOnDelete();
        });

        Schema::create('knowledge_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('workspace')->default('intelligence')->index();
            $table->string('query')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('event_type')->index();
            $table->string('subject_type')->index();
            $table->string('subject_id')->index();
            $table->json('payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_feedback', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('knowledge_session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('knowledge_session_id')->references('id')->on('knowledge_sessions')->nullOnDelete();
        });

        Schema::create('knowledge_learning_cycles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('workspace')->default('intelligence')->index();
            $table->string('status')->default('captured')->index();
            $table->string('outcome')->nullable();
            $table->decimal('confidence', 5, 2)->default(0.75);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->json('tools_used')->nullable();
            $table->json('verification_results')->nullable();
            $table->json('planner_decisions')->nullable();
            $table->text('feedback_summary')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_search_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('query');
            $table->string('workspace')->default('intelligence')->index();
            $table->unsignedInteger('latency_ms')->default(0);
            $table->unsignedInteger('result_count')->default(0);
            $table->json('filters')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_search_logs');
        Schema::dropIfExists('knowledge_learning_cycles');
        Schema::dropIfExists('knowledge_feedback');
        Schema::dropIfExists('knowledge_events');
        Schema::dropIfExists('knowledge_sessions');
        Schema::dropIfExists('knowledge_tags');
        Schema::dropIfExists('knowledge_memories');
        Schema::dropIfExists('knowledge_graph_edges');
        Schema::dropIfExists('knowledge_graph_nodes');
        Schema::dropIfExists('knowledge_relationships');
        Schema::dropIfExists('knowledge_entities');
        Schema::dropIfExists('knowledge_embeddings');
        Schema::dropIfExists('knowledge_documents');
        Schema::dropIfExists('knowledge_collections');
    }
};
