<?php

namespace App\Services\Builder;

use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BuilderRuntimeWriteBackupArtifactService
{
    protected const FORBIDDEN_RUNTIME_PATHS = [
        'app/Core',
        'modules/Core',
        'modules/SaaS',
        'modules/Updater',
        'modules/Installer',
        'vendor',
        'node_modules',
        'public/build',
        '.env',
        'composer.json',
        'package.json',
        'routes',
        'resources/js/app.js',
        'database/migrations',
    ];

    public function prepare(BuilderPublishExecution $execution): array
    {
        $execution->loadMissing('definition', 'approvalRequest');

        $checks = [];
        $blockers = [];
        $warnings = [];
        $backups = [];
        $metadata = $execution->metadata_json ?: [];

        $backupRoot = 'storage/app/builder-publish-backups/'.$execution->builder_definition_id.'/'.$execution->getKey();
        $backupManifestPath = $backupRoot.'/backup-manifest.json';
        $preflightPath = (string) ($metadata['runtime_write_execution_preflight_path'] ?? '');
        $planPath = (string) ($metadata['runtime_write_plan_path'] ?? '');
        $rollbackManifestPath = (string) $execution->rollback_manifest_path;

        $this->logAudit($execution, 'runtime_write_backup_started', [
            'runtime_write_preflight_path' => $preflightPath,
            'runtime_write_plan_path' => $planPath,
            'backup_root' => $backupRoot,
        ]);

        $preflightReport = $this->readJsonIfAllowed($preflightPath, 'storage/app/builder-runtime-write-preflights/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $runtimeWritePlan = $this->readJsonIfAllowed($planPath, 'storage/app/builder-runtime-write-plans/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $rollbackManifest = $this->readJsonIfAllowed($rollbackManifestPath, 'storage/app/builder-publish-rollbacks/'.$execution->builder_definition_id.'/'.$execution->getKey());

        $this->addCheck($checks, 'execution_status_runtime_write_preflight_passed', $execution->status === BuilderPublishExecution::STATUS_RUNTIME_WRITE_PREFLIGHT_PASSED, true, 'Execution status must be runtime_write_preflight_passed.', $blockers);
        $this->addCheck($checks, 'runtime_write_preflight_report_exists', is_array($preflightReport), true, 'Runtime write execution preflight report must exist under storage.', $blockers);
        $this->addCheck($checks, 'runtime_write_preflight_ready_true', ($preflightReport['ready_for_future_runtime_write'] ?? false) === true, true, 'Runtime write execution preflight must be ready.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_exists', is_array($runtimeWritePlan), true, 'Runtime write plan must exist under storage.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_valid_json', is_array($runtimeWritePlan), true, 'Runtime write plan JSON must be valid.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_exists', is_array($rollbackManifest), true, 'Rollback manifest draft must exist under storage.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_valid_json', is_array($rollbackManifest), true, 'Rollback manifest JSON must be valid.', $blockers);
        $this->addCheck($checks, 'backup_root_under_storage', $this->pathStartsWith($backupRoot, 'storage/app/builder-publish-backups/'.$execution->builder_definition_id.'/'.$execution->getKey()), true, 'Backup root must be under storage/app/builder-publish-backups for this execution.', $blockers);

        if (is_array($runtimeWritePlan)) {
            foreach (($runtimeWritePlan['planned_writes'] ?? []) as $index => $write) {
                $backups[] = $this->backupEntry($write, $index, $backupRoot, $blockers, $warnings);
            }
        }

        $this->addCheck($checks, 'no_runtime_paths_written', true, true, 'No runtime paths are written by backup artifact preparation.', $blockers);
        $this->addCheck($checks, 'no_staged_artifacts_copied', true, true, 'No staged artifacts are copied to runtime.', $blockers);
        $this->addCheck($checks, 'existing_files_backed_up_under_storage_only', $this->backupsUnderStorageOnly($backups, $backupRoot), true, 'Existing files must only be copied to backup storage.', $blockers);
        $this->addCheck($checks, 'planned_migrations_not_executed', $this->plannedMigrationsNotExecuted($backups), true, 'Planned migrations are recorded but not executed.', $blockers);
        $this->addCheck($checks, 'runtime_writes_zero', true, true, 'Runtime writes remain zero.', $blockers);
        $this->addCheck($checks, 'publish_executed_false', true, true, 'Publish is not executed.', $blockers);
        $this->addCheck($checks, 'copy_to_runtime_false', true, true, 'Copy to runtime is not executed.', $blockers);

        $safe = $blockers === [];
        $status = $safe
            ? BuilderPublishExecution::STATUS_RUNTIME_WRITE_BACKUPS_PREPARED
            : BuilderPublishExecution::STATUS_RUNTIME_WRITE_BACKUP_BLOCKED;

        $report = [
            'execution_id' => $execution->getKey(),
            'status' => $status,
            'safe' => $safe,
            'writes_performed' => 0,
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
            'runtime_module_effect' => 'none',
            'backup_root' => $backupRoot,
            'backup_manifest_path' => $backupManifestPath,
            'runtime_write_preflight_path' => $preflightPath,
            'runtime_write_plan_path' => $planPath,
            'rollback_manifest_path' => $rollbackManifestPath,
            'backups' => $backups,
            'summary' => $this->summary($backups),
            'checks' => $checks,
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique($warnings)),
            'forbidden_actions' => [
                'copy_staged_artifacts_to_runtime',
                'execute_runtime_write',
                'publish',
                'run_migrations',
                'register_routes',
                'execute_rollback',
            ],
            'next_allowed_actions' => [
                'review backup artifact',
                'future runtime write execution implementation after separate task',
            ],
        ];

        File::ensureDirectoryExists(base_path($backupRoot));
        File::put(base_path($backupManifestPath), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->updateRollbackManifestDraft($rollbackManifestPath, $report);

        $metadata['runtime_write_backup_manifest_path'] = $backupManifestPath;
        $metadata['runtime_write_backup_report'] = $report;

        $execution->fill([
            'status' => $status,
            'failure_reason' => $safe ? null : implode('; ', $report['blockers']),
            'metadata_json' => $metadata,
        ])->save();

        $this->logAudit($execution->fresh(), $safe ? 'runtime_write_backup_created' : 'runtime_write_backup_blocked', [
            'backup_manifest_path' => $backupManifestPath,
            'safe' => $safe,
            'blockers' => $report['blockers'],
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
        ]);

        return $report;
    }

    protected function backupEntry(array $write, int $index, string $backupRoot, array &$blockers, array &$warnings): array
    {
        $futureRuntimePath = str_replace('\\', '/', ltrim((string) ($write['future_runtime_path'] ?? ''), '/'));
        $writeAction = (string) ($write['write_action'] ?? 'skip');
        $pathSafe = $this->runtimePathSafe($futureRuntimePath);
        $fullRuntimePath = base_path($futureRuntimePath);
        $exists = $pathSafe && is_file($fullRuntimePath);
        $backupPath = null;
        $preWriteSha = null;
        $backupSha = null;
        $backupCreated = false;

        if (! $pathSafe) {
            $blockers[] = "Future runtime path is forbidden or unsafe: {$futureRuntimePath}";
        }

        if ($writeAction === 'planned_migration') {
            return [
                'future_runtime_path' => $futureRuntimePath,
                'existed_before' => false,
                'backup_path' => null,
                'pre_write_sha256' => null,
                'backup_sha256' => null,
                'backup_created' => false,
                'write_action' => 'planned_migration',
                'migration_execution_allowed_in_this_phase' => false,
                'runtime_written' => false,
            ];
        }

        if ($exists) {
            $preWriteSha = hash_file('sha256', $fullRuntimePath);
            $backupPath = $backupRoot.'/files/'.str_pad((string) $index, 4, '0', STR_PAD_LEFT).'-'.str_replace(['/', '\\', ':'], '-', $futureRuntimePath);
            File::ensureDirectoryExists(dirname(base_path($backupPath)));
            File::copy($fullRuntimePath, base_path($backupPath));
            $backupSha = hash_file('sha256', base_path($backupPath));
            $backupCreated = true;
        } elseif (($write['backup_required'] ?? false) === true && $writeAction === 'overwrite') {
            $blockers[] = "Planned overwrite target does not exist for backup: {$futureRuntimePath}";
        } elseif ($futureRuntimePath !== '' && $pathSafe && $writeAction === 'create') {
            $warnings[] = "Future runtime path does not exist; no backup needed: {$futureRuntimePath}";
        }

        return [
            'future_runtime_path' => $futureRuntimePath,
            'existed_before' => $exists,
            'backup_path' => $backupPath,
            'pre_write_sha256' => $preWriteSha,
            'backup_sha256' => $backupSha,
            'backup_created' => $backupCreated,
            'write_action' => $writeAction,
            'migration_execution_allowed_in_this_phase' => false,
            'runtime_written' => false,
        ];
    }

    protected function runtimePathSafe(string $path): bool
    {
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1) {
            return false;
        }

        foreach (self::FORBIDDEN_RUNTIME_PATHS as $forbidden) {
            if ($path === $forbidden || str_starts_with($path, rtrim($forbidden, '/').'/')) {
                return false;
            }
        }

        return true;
    }

    protected function backupsUnderStorageOnly(array $backups, string $backupRoot): bool
    {
        foreach ($backups as $backup) {
            $path = (string) ($backup['backup_path'] ?? '');
            if ($path !== '' && ! $this->pathStartsWith($path, $backupRoot)) {
                return false;
            }
        }

        return true;
    }

    protected function plannedMigrationsNotExecuted(array $backups): bool
    {
        foreach ($backups as $backup) {
            if (($backup['write_action'] ?? null) === 'planned_migration' && ($backup['migration_execution_allowed_in_this_phase'] ?? true) !== false) {
                return false;
            }
        }

        return true;
    }

    protected function summary(array $backups): array
    {
        $summary = [
            'total_planned_writes' => count($backups),
            'existing_files_backed_up' => 0,
            'new_files_no_backup_needed' => 0,
            'planned_migrations_no_execution' => 0,
            'blocked' => 0,
        ];

        foreach ($backups as $backup) {
            if (($backup['backup_created'] ?? false) === true) {
                $summary['existing_files_backed_up']++;
            }

            if (($backup['existed_before'] ?? false) === false && ($backup['write_action'] ?? null) === 'create') {
                $summary['new_files_no_backup_needed']++;
            }

            if (($backup['write_action'] ?? null) === 'planned_migration') {
                $summary['planned_migrations_no_execution']++;
            }

            if (($backup['runtime_written'] ?? true) !== false) {
                $summary['blocked']++;
            }
        }

        return $summary;
    }

    protected function updateRollbackManifestDraft(string $path, array $report): void
    {
        $manifest = $this->readJsonIfAllowed($path, 'storage/app/builder-publish-rollbacks/');

        if (! is_array($manifest)) {
            return;
        }

        $manifest['runtime_write_backups'] = [
            'backup_manifest_path' => $report['backup_manifest_path'],
            'backup_root' => $report['backup_root'],
            'backups' => $report['backups'],
            'summary' => $report['summary'],
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
            'updated_at' => now()->toIso8601String(),
        ];

        File::put(base_path($path), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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
