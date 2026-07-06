<?php

namespace App\Services\Builder;

use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteFinalConfirmation;
use App\Models\BuilderRuntimeWriteOperatorAcknowledgement;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class BuilderRuntimeWriteOperatorAcknowledgementService
{
    public const RUNBOOK_VERSION = 'runtime-write-operator-runbook-v1';

    public function listForExecution(BuilderPublishExecution $execution): Collection
    {
        return $execution->runtimeWriteOperatorAcknowledgements()
            ->latest()
            ->get();
    }

    public function request(BuilderPublishExecution $execution, ?array $metadata = []): array
    {
        $execution->loadMissing('definition');
        $executionStatusAllowed = in_array($execution->status, [
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_BLOCKED,
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_PASSED,
        ], true);

        if (! $executionStatusAllowed) {
            throw new RuntimeException('Operator acknowledgement requires execution status runtime_write_guard_blocked or runtime_write_guard_passed.');
        }

        $paths = $this->boundPaths($execution);
        if (! is_array($this->readStorageJson($paths['kill_switch_guard_path'], 'storage/app/builder-runtime-write-guards/'))) {
            throw new RuntimeException('Kill-switch guard report is missing or invalid.');
        }

        if (! is_array($this->readStorageJson($paths['post_backup_readiness_path'], 'storage/app/builder-runtime-write-readiness/'))) {
            throw new RuntimeException('Post-backup runtime write readiness report is missing or invalid.');
        }

        $acknowledgement = BuilderRuntimeWriteOperatorAcknowledgement::create([
            'uuid' => (string) Str::uuid(),
            'builder_definition_id' => $execution->builder_definition_id,
            'builder_publish_execution_id' => $execution->getKey(),
            'status' => BuilderRuntimeWriteOperatorAcknowledgement::STATUS_REQUESTED,
            'definition_checksum' => $execution->definition_checksum,
            'runtime_write_plan_path' => $paths['runtime_write_plan_path'],
            'post_backup_readiness_path' => $paths['post_backup_readiness_path'],
            'kill_switch_guard_path' => $paths['kill_switch_guard_path'],
            'backup_manifest_path' => $paths['backup_manifest_path'],
            'rollback_manifest_path' => $paths['rollback_manifest_path'],
            'runbook_version' => self::RUNBOOK_VERSION,
            'checklist_json' => $this->checklist(),
            'metadata_json' => $metadata ?: [],
        ]);

        $this->logAudit($acknowledgement, 'runtime_write_operator_acknowledgement_requested', [
            'runbook_version' => self::RUNBOOK_VERSION,
            'kill_switch_guard_path' => $paths['kill_switch_guard_path'],
        ]);

        return $this->report($acknowledgement->fresh());
    }

    public function acknowledge(BuilderRuntimeWriteOperatorAcknowledgement $acknowledgement, ?string $note = null): array
    {
        $freshness = $this->checkFreshness($acknowledgement);

        if (($freshness['safe'] ?? false) !== true) {
            $acknowledgement->fill([
                'status' => BuilderRuntimeWriteOperatorAcknowledgement::STATUS_INVALIDATED,
                'acknowledged_by_id' => auth()->id(),
                'acknowledged_at' => now(),
                'acknowledgement_note' => $note,
                'invalidation_reason' => implode('; ', $freshness['blockers'] ?? []),
            ])->save();

            $this->logAudit($acknowledgement->fresh(), 'runtime_write_operator_acknowledgement_invalidated', [
                'blockers' => $freshness['blockers'] ?? [],
            ]);

            return $this->report($acknowledgement->fresh(), $freshness['checks'] ?? [], $freshness['blockers'] ?? [], $freshness['warnings'] ?? []);
        }

        if ($acknowledgement->status !== BuilderRuntimeWriteOperatorAcknowledgement::STATUS_REQUESTED) {
            throw new RuntimeException('Only requested operator acknowledgements can be acknowledged.');
        }

        $acknowledgement->fill([
            'status' => BuilderRuntimeWriteOperatorAcknowledgement::STATUS_ACKNOWLEDGED,
            'acknowledged_by_id' => auth()->id(),
            'acknowledged_at' => now(),
            'acknowledgement_note' => $note,
        ])->save();

        $this->logAudit($acknowledgement->fresh(), 'runtime_write_operator_acknowledged');

        return $this->report($acknowledgement->fresh(), $freshness['checks'] ?? []);
    }

    public function revoke(BuilderRuntimeWriteOperatorAcknowledgement $acknowledgement, ?string $note = null): array
    {
        if (! in_array($acknowledgement->status, [
            BuilderRuntimeWriteOperatorAcknowledgement::STATUS_REQUESTED,
            BuilderRuntimeWriteOperatorAcknowledgement::STATUS_ACKNOWLEDGED,
        ], true)) {
            throw new RuntimeException('Only requested or acknowledged operator acknowledgements can be revoked.');
        }

        $acknowledgement->fill([
            'status' => BuilderRuntimeWriteOperatorAcknowledgement::STATUS_REVOKED,
            'acknowledged_by_id' => auth()->id(),
            'acknowledged_at' => now(),
            'acknowledgement_note' => $note,
        ])->save();

        $this->logAudit($acknowledgement->fresh(), 'runtime_write_operator_acknowledgement_revoked');

        return $this->report($acknowledgement->fresh());
    }

    public function checkFreshness(BuilderRuntimeWriteOperatorAcknowledgement $acknowledgement): array
    {
        $acknowledgement->loadMissing('definition', 'publishExecution');
        $checks = [];
        $blockers = [];
        $warnings = [];
        $execution = $acknowledgement->publishExecution;
        $definition = $acknowledgement->definition;
        $paths = $execution ? $this->boundPaths($execution) : [];

        $statusAllowed = $execution && in_array($execution->status, [
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_BLOCKED,
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_PASSED,
        ], true);

        $this->addCheck($checks, 'execution_exists', $execution !== null, true, 'Publish execution record must exist.', $blockers);
        $this->addCheck($checks, 'execution_status_runtime_write_guard', $statusAllowed, true, 'Execution status must remain runtime_write_guard_blocked or runtime_write_guard_passed.', $blockers);
        $this->addCheck($checks, 'definition_checksum_unchanged', $definition && $acknowledgement->definition_checksum === $definition->checksum, true, 'Definition checksum must be unchanged.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_path_unchanged', ($paths['runtime_write_plan_path'] ?? null) === $acknowledgement->runtime_write_plan_path, true, 'Runtime write plan path must be unchanged.', $blockers);
        $this->addCheck($checks, 'post_backup_readiness_path_unchanged', ($paths['post_backup_readiness_path'] ?? null) === $acknowledgement->post_backup_readiness_path, true, 'Post-backup readiness path must be unchanged.', $blockers);
        $this->addCheck($checks, 'kill_switch_guard_path_unchanged', ($paths['kill_switch_guard_path'] ?? null) === $acknowledgement->kill_switch_guard_path, true, 'Kill-switch guard path must be unchanged.', $blockers);
        $this->addCheck($checks, 'backup_manifest_path_unchanged', ($paths['backup_manifest_path'] ?? null) === $acknowledgement->backup_manifest_path, true, 'Backup manifest path must be unchanged.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_path_unchanged', ($paths['rollback_manifest_path'] ?? null) === $acknowledgement->rollback_manifest_path, true, 'Rollback manifest path must be unchanged.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_json_valid', is_array($this->readStorageJson((string) $acknowledgement->runtime_write_plan_path, 'storage/app/builder-runtime-write-plans/')), true, 'Runtime write plan JSON must exist and remain valid.', $blockers);
        $this->addCheck($checks, 'post_backup_readiness_json_valid', is_array($this->readStorageJson((string) $acknowledgement->post_backup_readiness_path, 'storage/app/builder-runtime-write-readiness/')), true, 'Post-backup readiness JSON must exist and remain valid.', $blockers);
        $this->addCheck($checks, 'kill_switch_guard_json_valid', is_array($this->readStorageJson((string) $acknowledgement->kill_switch_guard_path, 'storage/app/builder-runtime-write-guards/')), true, 'Kill-switch guard JSON must exist and remain valid.', $blockers);
        $this->addCheck($checks, 'backup_manifest_json_valid', is_array($this->readStorageJson((string) $acknowledgement->backup_manifest_path, 'storage/app/builder-publish-backups/')), true, 'Backup manifest JSON must exist and remain valid.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_json_valid', is_array($this->readStorageJson((string) $acknowledgement->rollback_manifest_path, 'storage/app/builder-publish-rollbacks/')), true, 'Rollback manifest JSON must exist and remain valid.', $blockers);
        $this->addCheck($checks, 'final_confirmation_still_granted', $this->latestConfirmationGranted($execution), true, 'Latest final confirmation must remain granted.', $blockers);
        $this->addCheck($checks, 'acknowledgement_not_expired', $acknowledgement->expires_at === null || $acknowledgement->expires_at->isFuture(), true, 'Operator acknowledgement must not be expired.', $blockers);
        $this->addCheck($checks, 'acknowledgement_does_not_publish', true, true, 'Operator acknowledgement does not publish.', $blockers);
        $this->addCheck($checks, 'acknowledgement_does_not_write_runtime', true, true, 'Operator acknowledgement does not write runtime files.', $blockers);
        $this->addCheck($checks, 'acknowledgement_does_not_override_kill_switch', true, true, 'Operator acknowledgement does not override the kill-switch.', $blockers);

        return [
            'safe' => $blockers === [],
            'checks' => $checks,
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique($warnings)),
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
        ];
    }

    protected function report(
        BuilderRuntimeWriteOperatorAcknowledgement $acknowledgement,
        array $checks = [],
        array $blockers = [],
        array $warnings = []
    ): array {
        return [
            'acknowledgement_id' => $acknowledgement->getKey(),
            'status' => $acknowledgement->status,
            'safe' => $blockers === [],
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
            'acknowledgement_does_not_publish' => true,
            'acknowledgement_does_not_write_runtime' => true,
            'acknowledgement_does_not_override_kill_switch' => true,
            'acknowledgement' => $acknowledgement->fresh(),
            'checks' => $checks,
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique($warnings)),
            'forbidden_actions' => [
                'execute_runtime_write',
                'copy_to_runtime',
                'publish',
                'run_migrations',
                'register_routes',
                'execute_rollback',
                'override_kill_switch',
            ],
            'next_allowed_actions' => [
                'review operator acknowledgement state',
                'future runtime write implementation after separate task',
            ],
        ];
    }

    protected function boundPaths(BuilderPublishExecution $execution): array
    {
        return [
            'runtime_write_plan_path' => (string) data_get($execution->metadata_json, 'runtime_write_plan_path'),
            'post_backup_readiness_path' => (string) data_get($execution->metadata_json, 'post_backup_runtime_write_readiness_path'),
            'kill_switch_guard_path' => (string) data_get($execution->metadata_json, 'runtime_write_kill_switch_guard_path'),
            'backup_manifest_path' => (string) data_get($execution->metadata_json, 'runtime_write_backup_manifest_path'),
            'rollback_manifest_path' => (string) $execution->rollback_manifest_path,
        ];
    }

    protected function checklist(): array
    {
        return collect([
            'confirmed_builder_definition',
            'confirmed_candidate_snapshot',
            'confirmed_approval_request',
            'confirmed_execution_record',
            'confirmed_staged_validation',
            'confirmed_runtime_write_plan',
            'confirmed_final_confirmation',
            'confirmed_runtime_write_preflight',
            'confirmed_backups_prepared',
            'confirmed_post_backup_readiness',
            'confirmed_kill_switch_guard',
            'confirmed_target_module_slug',
            'confirmed_no_core_saas_updater_installer_paths',
            'confirmed_backup_manifest',
            'confirmed_rollback_manifest',
            'confirmed_no_ai_autonomous_execution',
            'confirmed_no_mcp_bypass',
            'confirmed_runtime_write_not_executed_by_acknowledgement',
        ])->map(fn (string $key): array => [
            'key' => $key,
            'status' => 'acknowledged_by_human_operator',
            'required' => true,
        ])->all();
    }

    protected function latestConfirmationGranted(?BuilderPublishExecution $execution): bool
    {
        if (! $execution) {
            return false;
        }

        return $execution->runtimeWriteFinalConfirmations()
            ->where('status', BuilderRuntimeWriteFinalConfirmation::STATUS_GRANTED)
            ->latest()
            ->exists();
    }

    protected function addCheck(array &$checks, string $key, bool $passed, bool $required, string $message, array &$blockers): void
    {
        $status = $passed ? 'passed' : ($required ? 'blocked' : 'warning');
        $checks[] = compact('key', 'status', 'required', 'message');

        if (! $passed && $required) {
            $blockers[] = $message;
        }
    }

    protected function readStorageJson(string $path, string $prefix): ?array
    {
        $normalized = str_replace('\\', '/', ltrim($path, '/'));
        if ($normalized === '' || (! str_starts_with($normalized, rtrim($prefix, '/').'/') && $normalized !== rtrim($prefix, '/'))) {
            return null;
        }

        if (! is_file(base_path($normalized))) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents(base_path($normalized)), true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function logAudit(BuilderRuntimeWriteOperatorAcknowledgement $acknowledgement, string $eventType, array $payload = []): BuilderPublishAuditLog
    {
        return BuilderPublishAuditLog::create([
            'uuid' => (string) Str::uuid(),
            'builder_definition_id' => $acknowledgement->builder_definition_id,
            'builder_publish_approval_request_id' => $acknowledgement->publishExecution?->builder_publish_approval_request_id,
            'candidate_id' => $acknowledgement->publishExecution?->candidate_id,
            'definition_checksum' => $acknowledgement->definition_checksum,
            'event_type' => $eventType,
            'actor_id' => auth()->id(),
            'payload_json' => array_merge([
                'builder_runtime_write_operator_acknowledgement_id' => $acknowledgement->getKey(),
                'builder_publish_execution_id' => $acknowledgement->builder_publish_execution_id,
                'control_plane_only' => true,
                'runtime_writes_performed' => 0,
                'publish_executed' => false,
                'copy_to_runtime_executed' => false,
            ], $payload),
            'created_at' => now(),
        ]);
    }
}
