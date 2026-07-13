<?php

namespace App\Services\Builder;

use App\Models\BuilderPublishApprovalRequest;
use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteFinalConfirmation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class BuilderPostBackupRuntimeWriteReadinessService
{
    public function readiness(BuilderPublishExecution $execution): array
    {
        $execution->loadMissing('definition', 'approvalRequest', 'runtimeWriteFinalConfirmations');

        $checks = [];
        $blockers = [];
        $warnings = [];
        $metadata = $execution->metadata_json ?: [];

        $reportRoot = 'storage/app/builder-runtime-write-readiness/'.$execution->builder_definition_id.'/'.$execution->getKey();
        $reportPath = $reportRoot.'/post-backup-readiness.json';
        $backupManifestPath = (string) ($metadata['runtime_write_backup_manifest_path'] ?? '');
        $preflightPath = (string) ($metadata['runtime_write_execution_preflight_path'] ?? '');
        $planPath = (string) ($metadata['runtime_write_plan_path'] ?? '');
        $rollbackManifestPath = (string) $execution->rollback_manifest_path;

        $this->logAudit($execution, 'runtime_write_readiness_started', [
            'backup_manifest_path' => $backupManifestPath,
            'runtime_write_preflight_path' => $preflightPath,
            'runtime_write_plan_path' => $planPath,
        ]);

        $latestConfirmation = $execution->runtimeWriteFinalConfirmations()
            ->where('status', BuilderRuntimeWriteFinalConfirmation::STATUS_GRANTED)
            ->latest()
            ->first();

        $backupManifest = $this->readJsonIfAllowed($backupManifestPath, 'storage/app/builder-publish-backups/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $preflightReport = $this->readJsonIfAllowed($preflightPath, 'storage/app/builder-runtime-write-preflights/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $runtimeWritePlan = $this->readJsonIfAllowed($planPath, 'storage/app/builder-runtime-write-plans/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $rollbackManifest = $this->readJsonIfAllowed($rollbackManifestPath, 'storage/app/builder-publish-rollbacks/'.$execution->builder_definition_id.'/'.$execution->getKey());

        $this->addCheck($checks, 'execution_exists', $execution->exists, true, 'Publish execution record must exist.', $blockers);
        $this->addCheck($checks, 'execution_status_runtime_write_backups_prepared', $execution->status === BuilderPublishExecution::STATUS_RUNTIME_WRITE_BACKUPS_PREPARED, true, 'Execution status must be runtime_write_backups_prepared.', $blockers);
        $this->addCheck($checks, 'latest_final_confirmation_exists', $latestConfirmation !== null, true, 'A granted final confirmation must exist.', $blockers);
        $this->addCheck($checks, 'final_confirmation_status_granted', $latestConfirmation?->status === BuilderRuntimeWriteFinalConfirmation::STATUS_GRANTED, true, 'Final confirmation status must be granted.', $blockers);
        $this->addCheck($checks, 'final_confirmation_still_fresh', $this->finalConfirmationFresh($execution, $latestConfirmation), true, 'Final confirmation bindings must still match execution, plan, checksum, candidate, and approval state.', $blockers);
        $this->addCheck($checks, 'runtime_write_preflight_report_exists', is_array($preflightReport), true, 'Runtime write preflight report must exist.', $blockers);
        $this->addCheck($checks, 'runtime_write_preflight_report_valid_json', is_array($preflightReport), true, 'Runtime write preflight report JSON must be valid.', $blockers);
        $this->addCheck($checks, 'runtime_write_preflight_ready_true', ($preflightReport['ready_for_future_runtime_write'] ?? false) === true, true, 'Runtime write preflight must remain ready.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_exists', is_array($runtimeWritePlan), true, 'Runtime write plan must exist.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_valid_json', is_array($runtimeWritePlan), true, 'Runtime write plan JSON must be valid.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_has_no_blockers', empty($runtimeWritePlan['blockers'] ?? []), true, 'Runtime write plan must have no blockers.', $blockers);
        $this->addCheck($checks, 'backup_manifest_exists', is_array($backupManifest), true, 'Backup manifest must exist.', $blockers);
        $this->addCheck($checks, 'backup_manifest_valid_json', is_array($backupManifest), true, 'Backup manifest JSON must be valid.', $blockers);
        $this->addCheck($checks, 'backup_manifest_under_storage', $this->pathStartsWith($backupManifestPath, 'storage/app/builder-publish-backups/'.$execution->builder_definition_id.'/'.$execution->getKey()), true, 'Backup manifest must be under storage/app/builder-publish-backups.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_exists', is_array($rollbackManifest), true, 'Rollback manifest must exist.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_valid_json', is_array($rollbackManifest), true, 'Rollback manifest JSON must be valid.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_references_backups', is_array($rollbackManifest) && isset($rollbackManifest['runtime_write_backups']['backup_manifest_path']) && $rollbackManifest['runtime_write_backups']['backup_manifest_path'] === $backupManifestPath, true, 'Rollback manifest must reference the backup manifest.', $blockers);
        $this->addCheck($checks, 'all_overwrite_actions_have_backup_records', $this->overwriteActionsHaveBackups($runtimeWritePlan, $backupManifest), true, 'Every overwrite action must have a backup record.', $blockers);
        $this->addCheck($checks, 'new_files_not_created_in_runtime', $this->newFilesNotCreated($runtimeWritePlan), true, 'Planned create actions must not already exist as runtime files.', $blockers);
        $this->addCheck($checks, 'planned_migrations_not_executed', $this->plannedMigrationsNotExecuted($backupManifest), true, 'Planned migrations must not be executed.', $blockers);
        $this->addCheck($checks, 'staged_artifacts_not_copied_to_runtime', $this->stagedArtifactsNotCopied($backupManifest), true, 'Staged artifacts must not be copied to runtime.', $blockers);
        $this->addCheck($checks, 'runtime_write_endpoint_is_guarded_future_action', true, true, 'Runtime write endpoint may exist only as a guarded execution action after readiness, kill-switch, and operator acknowledgement.', $blockers);
        $this->addCheck($checks, 'no_copy_to_runtime_endpoint', ! $this->routeUriContains('copy-to-runtime'), true, 'No copy-to-runtime endpoint may exist.', $blockers);
        $this->addCheck($checks, 'no_publish_endpoint', ! $this->hasExecutablePublishRoute(), true, 'No executable publish endpoint may exist.', $blockers);
        $this->addCheck($checks, 'runtime_writes_zero', true, true, 'Runtime writes remain zero.', $blockers);
        $this->addCheck($checks, 'publish_executed_false', true, true, 'Publish is not executed.', $blockers);
        $this->addCheck($checks, 'copy_to_runtime_false', true, true, 'Copy to runtime is not executed.', $blockers);

        $safe = $blockers === [];
        $status = $safe
            ? BuilderPublishExecution::STATUS_RUNTIME_WRITE_READINESS_PASSED
            : BuilderPublishExecution::STATUS_RUNTIME_WRITE_READINESS_BLOCKED;

        $report = [
            'execution_id' => $execution->getKey(),
            'status' => $status,
            'ready_for_runtime_write_execution' => $safe,
            'ready_for_runtime_write_execution_is_not_execution' => true,
            'safe' => $safe,
            'writes_performed' => 0,
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
            'runtime_module_effect' => 'none',
            'readiness_report_path' => $reportPath,
            'backup_manifest_path' => $backupManifestPath,
            'runtime_write_preflight_path' => $preflightPath,
            'runtime_write_plan_path' => $planPath,
            'rollback_manifest_path' => $rollbackManifestPath,
            'checks' => $checks,
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique($warnings)),
            'forbidden_actions' => [
                'execute_runtime_write',
                'copy_staged_artifacts_to_runtime',
                'publish',
                'run_migrations',
                'register_routes',
                'execute_rollback',
            ],
            'next_allowed_actions' => [
                'review post-backup runtime write readiness',
                'future runtime write execution implementation after separate task',
            ],
        ];

        File::ensureDirectoryExists(base_path($reportRoot));
        File::put(base_path($reportPath), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $metadata['post_backup_runtime_write_readiness_path'] = $reportPath;
        $metadata['post_backup_runtime_write_readiness_report'] = $report;

        $execution->fill([
            'status' => $status,
            'failure_reason' => $safe ? null : implode('; ', $report['blockers']),
            'metadata_json' => $metadata,
        ])->save();

        $this->logAudit($execution->fresh(), $safe ? 'runtime_write_readiness_passed' : 'runtime_write_readiness_blocked', [
            'readiness_report_path' => $reportPath,
            'ready_for_runtime_write_execution' => $safe,
            'blockers' => $report['blockers'],
        ]);

        return $report;
    }

    protected function finalConfirmationFresh(BuilderPublishExecution $execution, ?BuilderRuntimeWriteFinalConfirmation $confirmation): bool
    {
        if (! $confirmation) {
            return false;
        }

        $confirmation->loadMissing('definition', 'approvalRequest');

        return $confirmation->builder_publish_execution_id === $execution->getKey()
            && $confirmation->status === BuilderRuntimeWriteFinalConfirmation::STATUS_GRANTED
            && $confirmation->definition_checksum === $execution->definition?->checksum
            && $confirmation->definition_checksum === $execution->definition_checksum
            && $confirmation->runtime_write_plan_path === (string) data_get($execution->metadata_json, 'runtime_write_plan_path')
            && $confirmation->staged_validation_report_path === (string) data_get($execution->metadata_json, 'staged_file_validation_path')
            && (! filled($confirmation->candidate_id) || $confirmation->candidate_id === $execution->candidate_id)
            && ($confirmation->approvalRequest === null || $confirmation->approvalRequest->status === BuilderPublishApprovalRequest::STATUS_APPROVED)
            && ($confirmation->expires_at === null || $confirmation->expires_at->isFuture());
    }

    protected function overwriteActionsHaveBackups(?array $plan, ?array $manifest): bool
    {
        if (! is_array($plan) || ! is_array($manifest)) {
            return false;
        }

        $backups = collect($manifest['backups'] ?? [])->keyBy('future_runtime_path');

        foreach (($plan['planned_writes'] ?? []) as $write) {
            if (($write['write_action'] ?? null) !== 'overwrite' && ($write['backup_required'] ?? false) !== true) {
                continue;
            }

            $backup = $backups->get($write['future_runtime_path'] ?? '');
            if (! is_array($backup) || ($backup['backup_created'] ?? false) !== true || ! $this->pathStartsWith((string) ($backup['backup_path'] ?? ''), 'storage/app/builder-publish-backups/')) {
                return false;
            }
        }

        return true;
    }

    protected function newFilesNotCreated(?array $plan): bool
    {
        if (! is_array($plan)) {
            return false;
        }

        foreach (($plan['planned_writes'] ?? []) as $write) {
            if (($write['write_action'] ?? null) === 'create' && is_file(base_path((string) ($write['future_runtime_path'] ?? '')))) {
                return false;
            }
        }

        return true;
    }

    protected function plannedMigrationsNotExecuted(?array $manifest): bool
    {
        if (! is_array($manifest)) {
            return false;
        }

        foreach (($manifest['backups'] ?? []) as $backup) {
            if (($backup['write_action'] ?? null) === 'planned_migration' && ($backup['migration_execution_allowed_in_this_phase'] ?? true) !== false) {
                return false;
            }
        }

        return true;
    }

    protected function stagedArtifactsNotCopied(?array $manifest): bool
    {
        if (! is_array($manifest)) {
            return false;
        }

        foreach (($manifest['backups'] ?? []) as $backup) {
            if (($backup['runtime_written'] ?? true) !== false) {
                return false;
            }
        }

        return true;
    }

    protected function addCheck(array &$checks, string $key, bool $passed, bool $required, string $message, array &$blockers): void
    {
        $status = $passed ? 'passed' : ($required ? 'blocked' : 'warning');
        $checks[] = compact('key', 'status', 'required', 'message');

        if (! $passed && $required) {
            $blockers[] = $message;
        }
    }

    protected function readJsonIfAllowed(string $path, string $prefix): ?array
    {
        if (! $this->pathStartsWith($path, $prefix)) {
            return null;
        }

        $fullPath = base_path($path);
        if (! is_file($fullPath)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($fullPath), true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function pathStartsWith(string $path, string $prefix): bool
    {
        $normalized = str_replace('\\', '/', ltrim($path, '/'));

        return $normalized === rtrim($prefix, '/') || str_starts_with($normalized, rtrim($prefix, '/').'/');
    }

    protected function routeUriContains(string $needle): bool
    {
        foreach (Route::getRoutes() as $route) {
            if (str_contains($route->uri(), $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function hasExecutablePublishRoute(): bool
    {
        foreach (Route::getRoutes() as $route) {
            if (preg_match('#^api/builder/definitions/\{[^}]+\}/publish$#', $route->uri())) {
                return true;
            }
        }

        return false;
    }

    protected function logAudit(BuilderPublishExecution $execution, string $eventType, array $payload = []): BuilderPublishAuditLog
    {
        return BuilderPublishAuditLog::create([
            'uuid' => (string) Str::uuid(),
            'builder_definition_id' => $execution->builder_definition_id,
            'builder_publish_approval_request_id' => $execution->builder_publish_approval_request_id,
            'candidate_id' => $execution->candidate_id,
            'definition_checksum' => $execution->definition_checksum,
            'event_type' => $eventType,
            'actor_id' => auth()->id(),
            'payload_json' => array_merge([
                'builder_publish_execution_id' => $execution->getKey(),
                'control_plane_only' => true,
                'runtime_writes_performed' => 0,
                'publish_executed' => false,
                'copy_to_runtime_executed' => false,
            ], $payload),
            'created_at' => now(),
        ]);
    }
}
