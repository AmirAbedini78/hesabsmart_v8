<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('builder_runtime_write_final_confirmations', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('builder_definition_id');
            $table->unsignedBigInteger('builder_publish_execution_id');
            $table->unsignedBigInteger('builder_publish_approval_request_id')->nullable();
            $table->string('status')->index('brwfc_status_idx');
            $table->string('candidate_id')->nullable()->index('brwfc_candidate_idx');
            $table->string('definition_checksum')->nullable()->index('brwfc_checksum_idx');
            $table->text('runtime_write_plan_path')->nullable();
            $table->text('staged_validation_report_path')->nullable();
            $table->text('candidate_snapshot_path')->nullable();
            $table->json('approved_candidate_preflight_json')->nullable();
            $table->json('runtime_write_plan_json')->nullable();
            $table->unsignedBigInteger('requested_by_id')->nullable();
            $table->unsignedBigInteger('decided_by_id')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->text('invalidation_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index('builder_definition_id', 'brwfc_definition_idx');
            $table->foreign('builder_definition_id', 'brwfc_definition_fk')
                ->references('id')->on('builder_definitions')->cascadeOnDelete();

            $table->index('builder_publish_execution_id', 'brwfc_execution_idx');
            $table->foreign('builder_publish_execution_id', 'brwfc_execution_fk')
                ->references('id')->on('builder_publish_executions')->cascadeOnDelete();

            $table->index('builder_publish_approval_request_id', 'brwfc_approval_idx');
            $table->foreign('builder_publish_approval_request_id', 'brwfc_approval_fk')
                ->references('id')->on('builder_publish_approval_requests')->nullOnDelete();

            $table->index('requested_by_id', 'brwfc_requested_by_idx');
            $table->foreign('requested_by_id', 'brwfc_requested_by_fk')
                ->references('id')->on('users')->nullOnDelete();

            $table->index('decided_by_id', 'brwfc_decided_by_idx');
            $table->foreign('decided_by_id', 'brwfc_decided_by_fk')
                ->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builder_runtime_write_final_confirmations');
    }
};
