<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('builder_runtime_write_operator_acknowledgements')) {
            Schema::table('builder_runtime_write_operator_acknowledgements', function (Blueprint $table) {
                if (! Schema::hasColumn('builder_runtime_write_operator_acknowledgements', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->nullable();
                    $table->index('tenant_id', 'brwoa_tenant_idx');
                    $table->foreign('tenant_id', 'brwoa_tenant_fk')
                        ->references('id')->on('tenants')->cascadeOnDelete();
                }
            });

            return;
        }

        Schema::create('builder_runtime_write_operator_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('builder_definition_id');
            $table->unsignedBigInteger('builder_publish_execution_id');
            $table->string('status')->index('brwoa_status_idx');
            $table->string('definition_checksum')->nullable()->index('brwoa_checksum_idx');
            $table->text('runtime_write_plan_path')->nullable();
            $table->text('post_backup_readiness_path')->nullable();
            $table->text('kill_switch_guard_path')->nullable();
            $table->text('backup_manifest_path')->nullable();
            $table->text('rollback_manifest_path')->nullable();
            $table->string('runbook_version')->nullable();
            $table->json('checklist_json')->nullable();
            $table->unsignedBigInteger('acknowledged_by_id')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('acknowledgement_note')->nullable();
            $table->text('invalidation_reason')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index('builder_definition_id', 'brwoa_definition_idx');
            $table->index('tenant_id', 'brwoa_tenant_idx');
            $table->foreign('tenant_id', 'brwoa_tenant_fk')
                ->references('id')->on('tenants')->cascadeOnDelete();

            $table->foreign('builder_definition_id', 'brwoa_definition_fk')
                ->references('id')->on('builder_definitions')->cascadeOnDelete();

            $table->index('builder_publish_execution_id', 'brwoa_execution_idx');
            $table->foreign('builder_publish_execution_id', 'brwoa_execution_fk')
                ->references('id')->on('builder_publish_executions')->cascadeOnDelete();

            $table->index('acknowledged_by_id', 'brwoa_ack_by_idx');
            $table->foreign('acknowledged_by_id', 'brwoa_ack_by_fk')
                ->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('builder_runtime_write_operator_acknowledgements');
    }
};
