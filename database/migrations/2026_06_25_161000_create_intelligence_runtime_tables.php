<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('provider')->index();
            $table->string('model')->index();
            $table->string('status')->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('conversation_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->index();
            $table->string('role')->index();
            $table->string('type')->default('text')->index();
            $table->longText('content');
            $table->json('citations')->nullable();
            $table->json('tool_calls')->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
        });

        Schema::create('conversation_attachments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->index();
            $table->uuid('conversation_message_id')->nullable()->index();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
            $table->foreign('conversation_message_id')->references('id')->on('conversation_messages')->nullOnDelete();
        });

        Schema::create('provider_usage', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->nullable()->index();
            $table->uuid('conversation_message_id')->nullable()->index();
            $table->string('provider')->index();
            $table->string('model')->index();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('success')->default(true)->index();
            $table->string('error_message')->nullable();
            $table->decimal('cost', 12, 6)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->nullOnDelete();
            $table->foreign('conversation_message_id')->references('id')->on('conversation_messages')->nullOnDelete();
        });

        Schema::create('conversation_exports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->index();
            $table->string('format')->default('json')->index();
            $table->string('status')->default('pending')->index();
            $table->longText('payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
        });

        Schema::create('tool_executions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id')->nullable()->index();
            $table->uuid('conversation_message_id')->nullable()->index();
            $table->string('tool_name')->index();
            $table->string('status')->default('pending')->index();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('conversation_id')->references('id')->on('conversations')->nullOnDelete();
            $table->foreign('conversation_message_id')->references('id')->on('conversation_messages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_executions');
        Schema::dropIfExists('conversation_exports');
        Schema::dropIfExists('provider_usage');
        Schema::dropIfExists('conversation_attachments');
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
    }
};
