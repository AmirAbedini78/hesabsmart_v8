<?php

namespace App\Services\Builder;

use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteOperatorAcknowledgement;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BuilderRuntimeWriteExecutionService
{
    protected const FORBIDDEN_RUNTIME_PATHS = [
        'app/Core',
        'modules/Core',
        'modules/SaaS',
        'modules/Updater',
        'modules/Installer',
        'public/build',
        'vendor',
        'node_modules',
        'resources/js/app.js',
        'routes/web.php',
        '.env',
        'composer.json',
        'package.json',
    ];

    protected const ALLOWED_GENERATED_MODULE_PATH_SEGMENTS = [
        'App/Models',
        'App/Http/Controllers',
        'App/Http/Resources',
        'database/migrations',
        'resources/js',
        'routes',
    ];

    public function __construct(
        protected BuilderRuntimeWriteOperatorAcknowledgementService $operatorAcknowledgementService
    ) {
    }

    public function execute(BuilderPublishExecution $execution): array
    {
        $execution->loadMissing('definition', 'approvalRequest', 'runtimeWriteOperatorAcknowledgements');

        $this->logAudit($execution, 'runtime_write_execute_requested', [
            'runtime_write_enabled' => $this->booleanConfig(config('builder.runtime_write.enabled', false)),
        ]);

        $lock = Cache::lock('builder:runtime-write:'.$execution->getKey(), 120);
        $lockAcquired = false;
        $started = false;
        $committedFiles = [];

        try {
            $preflight = $this->preconditions($execution);

            if (($preflight['safe'] ?? false) !== true) {
                return $this->abort($execution, $preflight['checks'], $preflight['blockers'], $preflight['warnings']);
            }

            $lockAcquired = $this->acquireLock($lock);
            if (! $lockAcquired) {
                return $this->abort($execution, $preflight['checks'], ['Runtime write lock could not be acquired.'], $preflight['warnings']);
            }

            $execution->fill([
                'status' => BuilderPublishExecution::STATUS_RUNTIME_WRITE_STARTED,
                'started_at' => now(),
                'failure_reason' => null,
            ])->save();
            $started = true;

            $this->logAudit($execution->fresh(), 'runtime_write_started', [
                'lock_key' => 'builder:runtime-write:'.$execution->getKey(),
            ]);

            $plan = $preflight['runtime_write_plan'];
            $stagingRoot = (string) $execution->staging_root;

            foreach (($plan['planned_writes'] ?? []) as $index => $write) {
                if (($write['write_action'] ?? null) === 'skip') {
                    continue;
                }

                $committedFiles[] = $this->commitPlannedWrite($execution->fresh(), $write, $index, $stagingRoot, $preflight['backup_manifest']);
            }

            $report = $this->writeReport(
                $execution->fresh(),
                BuilderPublishExecution::STATUS_RUNTIME_WRITE_SUCCEEDED,
                true,
                $committedFiles,
                $preflight['checks'],
                [],
                $preflight['warnings']
            );

            $this->updateRollbackManifest((string) $execution->rollback_manifest_path, $report);

            $metadata = $execution->metadata_json ?: [];
            $metadata['runtime_write_report_path'] = $report['runtime_write_report_path'];
            $metadata['runtime_write_report'] = $report;
            $metadata['runtime_write_committed_files'] = $committedFiles;

            $execution->fill([
                'status' => BuilderPublishExecution::STATUS_RUNTIME_WRITE_SUCCEEDED,
                'failure_reason' => null,
                'metadata_json' => $metadata,
            ])->save();

            $this->logAudit($execution->fresh(), 'runtime_write_succeeded', [
                'runtime_write_report_path' => $report['runtime_write_report_path'],
                'runtime_writes_performed' => count($committedFiles),
                'publish_executed' => false,
                'migrations_run' => false,
            ]);

            return $report;
        } catch (Throwable $exception) {
            $status = $started
                ? BuilderPublishExecution::STATUS_RUNTIME_WRITE_FAILED
                : BuilderPublishExecution::STATUS_RUNTIME_WRITE_ABORTED;

            $report = $this->writeReport(
                $execution->fresh(),
                $status,
                false,
                $committedFiles,
                [],
                [$exception->getMessage()],
                []
            );

            $metadata = $execution->metadata_json ?: [];
            $metadata['runtime_write_report_path'] = $report['runtime_write_report_path'];
            $metadata['runtime_write_report'] = $report;
            $metadata['runtime_write_committed_files'] = $committedFiles;

            $execution->fill([
                'status' => $status,
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
                'metadata_json' => $metadata,
            ])->save();

            $this->logAudit($execution->fresh(), $started ? 'runtime_write_failed' : 'runtime_write_aborted', [
                'failure_reason' => $exception->getMessage(),
                'runtime_write_report_path' => $report['runtime_write_report_path'],
                'runtime_writes_performed' => count($committedFiles),
                'publish_executed' => false,
                'migrations_run' => false,
            ]);

            return $report;
        } finally {
            if ($lockAcquired) {
                $lock->release();
            }
        }
    }

    protected function preconditions(BuilderPublishExecution $execution): array
    {
        $checks = [];
        $blockers = [];
        $warnings = [];
        $metadata = $execution->metadata_json ?: [];

        $runtimeWriteEnabled = $this->booleanConfig(config('builder.runtime_write.enabled', false));
        $maxFiles = (int) config('builder.runtime_write.max_files_per_execution', 25);
        $maxBytes = (int) config('builder.runtime_write.max_total_bytes_per_execution', 5242880);
        $readinessPath = (string) ($metadata['post_backup_runtime_write_readiness_path'] ?? '');
        $guardPath = (string) ($metadata['runtime_write_kill_switch_guard_path'] ?? '');
        $planPath = (string) ($metadata['runtime_write_plan_path'] ?? '');
        $backupManifestPath = (string) ($metadata['runtime_write_backup_manifest_path'] ?? '');
        $rollbackManifestPath = (string) $execution->rollback_manifest_path;

        $readiness = $this->readJsonIfAllowed($readinessPath, 'storage/app/builder-runtime-write-readiness/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $guard = $this->readJsonIfAllowed($guardPath, 'storage/app/builder-runtime-write-guards/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $plan = $this->readJsonIfAllowed($planPath, 'storage/app/builder-runtime-write-plans/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $backupManifest = $this->readJsonIfAllowed($backupManifestPath, 'storage/app/builder-publish-backups/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $rollbackManifest = $this->readJsonIfAllowed($rollbackManifestPath, 'storage/app/builder-publish-rollbacks/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $latestAcknowledgement = $execution->runtimeWriteOperatorAcknowledgements()
            ->where('status', BuilderRuntimeWriteOperatorAcknowledgement::STATUS_ACKNOWLEDGED)
            ->latest()
            ->first();
        $ackFreshness = $latestAcknowledgement
            ? $this->operatorAcknowledgementService->checkFreshness($latestAcknowledgement)
            : ['safe' => false, 'checks' => [], 'blockers' => ['An acknowledged operator runbook is required.'], 'warnings' => []];
        $plannedWrites = is_array($plan) ? ($plan['planned_writes'] ?? []) : [];
        $totalBytes = $this->plannedBytes($plannedWrites, (string) $execution->staging_root);

        $this->addCheck($checks, 'config_runtime_write_enabled', $runtimeWriteEnabled, true, 'Runtime write is disabled by builder.runtime_write.enabled.', $blockers);
        $this->addCheck($checks, 'execution_status_runtime_write_operator_acknowledged', $execution->status === BuilderPublishExecution::STATUS_RUNTIME_WRITE_OPERATOR_ACKNOWLEDGED || (($ackFreshness['safe'] ?? false) === true), true, 'Execution must be runtime_write_operator_acknowledged or have a fresh acknowledged operator runbook.', $blockers);
        $this->addCheck($checks, 'post_backup_readiness_report_exists', is_array($readiness), true, 'Post-backup readiness report must exist.', $blockers);
        $this->addCheck($checks, 'post_backup_readiness_ready_true', ($readiness['ready_for_runtime_write_execution'] ?? false) === true, true, 'Post-backup readiness must be ready.', $blockers);
        $this->addCheck($checks, 'kill_switch_guard_report_exists', is_array($guard), true, 'Kill-switch guard report must exist.', $blockers);
        $this->addCheck($checks, 'kill_switch_guard_passed', ($guard['runtime_write_guard_passed'] ?? false) === true, true, 'Kill-switch guard must be passed.', $blockers);
        $this->addCheck($checks, 'operator_acknowledgement_exists', $latestAcknowledgement !== null, true, 'Latest operator acknowledgement must exist.', $blockers);
        $this->addCheck($checks, 'operator_acknowledgement_acknowledged', $latestAcknowledgement?->status === BuilderRuntimeWriteOperatorAcknowledgement::STATUS_ACKNOWLEDGED, true, 'Latest operator acknowledgement must be acknowledged.', $blockers);
        $this->addCheck($checks, 'operator_acknowledgement_fresh', ($ackFreshness['safe'] ?? false) === true, true, 'Latest operator acknowledgement must be fresh.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_valid_json', is_array($plan), true, 'Runtime write plan must exist and be valid JSON.', $blockers);
        $this->addCheck($checks, 'backup_manifest_valid_json', is_array($backupManifest), true, 'Backup manifest must exist and be valid JSON.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_valid_json', is_array($rollbackManifest), true, 'Rollback manifest must exist and be valid JSON.', $blockers);
        $this->addCheck($checks, 'runtime_path_allowlist_applied', $this->plannedWritesAllowed($plannedWrites), true, 'All planned runtime target paths must be allowlisted.', $blockers);
        $this->addCheck($checks, 'no_forbidden_runtime_paths', ! $this->hasForbiddenRuntimePath($plannedWrites), true, 'No planned target path may use forbidden runtime scopes.', $blockers);
        $this->addCheck($checks, 'no_path_traversal', ! $this->hasPathTraversal($plannedWrites), true, 'No planned source or target path may contain traversal.', $blockers);
        $this->addCheck($checks, 'max_files_per_execution', count($plannedWrites) <= $maxFiles, true, 'Planned file count must not exceed configured limit.', $blockers);
        $this->addCheck($checks, 'max_total_bytes_per_execution', $totalBytes <= $maxBytes, true, 'Planned byte count must not exceed configured limit.', $blockers);
        $this->addCheck($checks, 'overwrite_actions_have_backup_records', $this->overwriteActionsHaveBackups($plannedWrites, $backupManifest), true, 'Overwrite actions must have backup records.', $blockers);
        $this->addCheck($checks, 'migrations_written_as_files_only', $this->migrationsAreFilesOnly($plannedWrites), true, 'Migration files may be written as files only and must not be executed.', $blockers);
        $this->addCheck($checks, 'runtime_write_does_not_publish', true, true, 'Runtime write does not publish.', $blockers);
        $this->addCheck($checks, 'runtime_write_does_not_execute_rollback', true, true, 'Runtime write does not execute rollback.', $blockers);

        foreach (($ackFreshness['checks'] ?? []) as $check) {
            $checks[] = [
                'key' => 'operator_acknowledgement_'.$check['key'],
                'status' => $check['status'],
                'required' => $check['required'],
                'message' => $check['message'],
            ];
        }

        foreach (($ackFreshness['blockers'] ?? []) as $blocker) {
            $blockers[] = $blocker;
        }

        return [
            'safe' => $blockers === [],
            'checks' => $checks,
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique(array_merge($warnings, $ackFreshness['warnings'] ?? []))),
            'runtime_write_plan' => $plan,
            'backup_manifest' => $backupManifest,
            'rollback_manifest' => $rollbackManifest,
        ];
    }

    protected function abort(BuilderPublishExecution $execution, array $checks, array $blockers, array $warnings): array
    {
        $report = $this->writeReport(
            $execution,
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_ABORTED,
            false,
            [],
            $checks,
            $blockers,
            $warnings
        );

        $metadata = $execution->metadata_json ?: [];
        $metadata['runtime_write_report_path'] = $report['runtime_write_report_path'];
        $metadata['runtime_write_report'] = $report;
        $metadata['runtime_write_committed_files'] = [];

        $execution->fill([
            'status' => BuilderPublishExecution::STATUS_RUNTIME_WRITE_ABORTED,
            'failed_at' => now(),
            'failure_reason' => implode('; ', $report['blockers']),
            'metadata_json' => $metadata,
        ])->save();

        $this->logAudit($execution->fresh(), 'runtime_write_aborted', [
            'runtime_write_report_path' => $report['runtime_write_report_path'],
            'blockers' => $report['blockers'],
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
        ]);

        return $report;
    }

    protected function commitPlannedWrite(BuilderPublishExecution $execution, array $write, int $index, string $stagingRoot, ?array $backupManifest): array
    {
        $sourceRelativePath = str_replace('\\', '/', ltrim((string) ($write['source_relative_path'] ?? ''), '/'));
        $targetRelativePath = str_replace('\\', '/', ltrim((string) ($write['future_runtime_path'] ?? ''), '/'));
        $sourcePath = $stagingRoot.'/'.$sourceRelativePath;
        $sourceFullPath = base_path($sourcePath);
        $targetFullPath = base_path($targetRelativePath);

        if (! $this->pathStartsWith($sourcePath, $stagingRoot) || str_contains($sourceRelativePath, '..')) {
            throw new RuntimeException("Staged source path is unsafe: {$sourceRelativePath}");
        }

        if (! is_file($sourceFullPath)) {
            throw new RuntimeException("Staged source file does not exist: {$sourceRelativePath}");
        }

        if (! $this->runtimePathAllowed($targetRelativePath) || $this->matchesForbiddenRuntimePath($targetRelativePath)) {
            throw new RuntimeException("Runtime target path is not allowlisted: {$targetRelativePath}");
        }

        if (($write['write_action'] ?? null) === 'overwrite' && ! $this->hasBackupRecord($targetRelativePath, $backupManifest)) {
            throw new RuntimeException("Overwrite target is missing backup record: {$targetRelativePath}");
        }

        File::ensureDirectoryExists(dirname($targetFullPath));

        $tempPath = $targetRelativePath.'.tmp-'.$execution->getKey().'-'.$index.'-'.Str::random(8);
        $tempFullPath = base_path($tempPath);
        File::copy($sourceFullPath, $tempFullPath);

        $sourceSha = hash_file('sha256', $sourceFullPath);
        $tempSha = hash_file('sha256', $tempFullPath);

        $this->logAudit($execution, 'runtime_write_file_temp_created', [
            'source_relative_path' => $sourceRelativePath,
            'future_runtime_path' => $targetRelativePath,
            'temp_path' => $tempPath,
        ]);

        if ($sourceSha !== $tempSha || (($write['source_sha256'] ?? null) && $sourceSha !== $write['source_sha256'])) {
            File::delete($tempFullPath);

            throw new RuntimeException("Runtime write checksum mismatch for {$targetRelativePath}");
        }

        $this->logAudit($execution, 'runtime_write_file_hash_verified', [
            'source_relative_path' => $sourceRelativePath,
            'future_runtime_path' => $targetRelativePath,
            'sha256' => $sourceSha,
        ]);

        if (! @rename($tempFullPath, $targetFullPath)) {
            File::delete($tempFullPath);

            throw new RuntimeException("Runtime write atomic rename failed for {$targetRelativePath}");
        }

        $committedSha = hash_file('sha256', $targetFullPath);
        if ($committedSha !== $sourceSha) {
            throw new RuntimeException("Committed file checksum mismatch for {$targetRelativePath}");
        }

        $entry = [
            'source_relative_path' => $sourceRelativePath,
            'source_sha256' => $sourceSha,
            'future_runtime_path' => $targetRelativePath,
            'committed_sha256' => $committedSha,
            'write_action' => $write['write_action'] ?? 'create',
            'migration_execution_allowed_in_this_phase' => false,
            'runtime_written' => true,
            'published' => false,
            'migrations_run' => false,
        ];

        $this->logAudit($execution, 'runtime_write_file_committed', $entry);

        return $entry;
    }

    protected function writeReport(
        BuilderPublishExecution $execution,
        string $status,
        bool $runtimeWriteExecuted,
        array $committedFiles,
        array $checks,
        array $blockers,
        array $warnings
    ): array {
        $reportRoot = 'storage/app/builder-runtime-write-executions/'.$execution->builder_definition_id.'/'.$execution->getKey();
        $reportPath = $reportRoot.'/runtime-write-report.json';

        $report = [
            'execution_id' => $execution->getKey(),
            'status' => $status,
            'runtime_write_executed' => $runtimeWriteExecuted,
            'safe' => $blockers === [],
            'writes_performed' => count($committedFiles),
            'runtime_writes_performed' => count($committedFiles),
            'files_committed' => $committedFiles,
            'publish_executed' => false,
            'copy_to_runtime_executed' => $runtimeWriteExecuted && count($committedFiles) > 0,
            'migrations_run' => false,
            'routes_registered' => false,
            'module_marked_published' => false,
            'rollback_executed' => false,
            'runtime_write_report_path' => $reportPath,
            'checks' => $checks,
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique($warnings)),
            'forbidden_actions' => [
                'publish',
                'run_migrations',
                'register_routes',
                'mark_module_published',
                'execute_rollback',
                'write_core_saas_updater_installer',
                'write_public_build',
                'write_vendor',
                'write_node_modules',
            ],
            'next_allowed_actions' => [
                'review runtime write report',
                'run post-write smoke in a separate task',
            ],
        ];

        File::ensureDirectoryExists(base_path($reportRoot));
        File::put(base_path($reportPath), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $report;
    }

    protected function updateRollbackManifest(string $path, array $report): void
    {
        $manifest = $this->readJsonIfAllowed($path, 'storage/app/builder-publish-rollbacks/');

        if (! is_array($manifest)) {
            return;
        }

        $manifest['runtime_write_execution'] = [
            'runtime_write_report_path' => $report['runtime_write_report_path'],
            'committed_file_entries' => $report['files_committed'],
            'runtime_writes_performed' => $report['runtime_writes_performed'],
            'publish_executed' => false,
            'migrations_run' => false,
            'routes_registered' => false,
            'module_marked_published' => false,
            'rollback_executed' => false,
            'updated_at' => now()->toIso8601String(),
        ];
        $manifest['committed_file_entries'] = $report['files_committed'];

        File::put(base_path($path), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function acquireLock(Lock $lock): bool
    {
        return (bool) $lock->get();
    }

    protected function plannedWritesAllowed(array $plannedWrites): bool
    {
        foreach ($plannedWrites as $write) {
            if (($write['write_action'] ?? null) === 'skip') {
                continue;
            }

            $path = (string) ($write['future_runtime_path'] ?? '');
            if (($write['runtime_path_allowed'] ?? false) !== true || ! $this->runtimePathAllowed($path)) {
                return false;
            }
        }

        return true;
    }

    protected function runtimePathAllowed(string $path): bool
    {
        $path = str_replace('\\', '/', ltrim($path, '/'));

        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1) {
            return false;
        }

        if ($this->matchesForbiddenRuntimePath($path)) {
            return false;
        }

        if (! preg_match('#^modules/([^/]+)/(.+)$#', $path, $matches)) {
            return false;
        }

        $module = $matches[1] ?? '';
        if (in_array($module, ['Core', 'SaaS', 'Updater', 'Installer'], true)) {
            return false;
        }

        $insideModule = $matches[2] ?? '';
        foreach (self::ALLOWED_GENERATED_MODULE_PATH_SEGMENTS as $segment) {
            if ($insideModule === $segment || str_starts_with($insideModule, rtrim($segment, '/').'/')) {
                return true;
            }
        }

        return false;
    }

    protected function hasForbiddenRuntimePath(array $plannedWrites): bool
    {
        foreach ($plannedWrites as $write) {
            if ($this->matchesForbiddenRuntimePath((string) ($write['future_runtime_path'] ?? ''))) {
                return true;
            }
        }

        return false;
    }

    protected function matchesForbiddenRuntimePath(string $path): bool
    {
        $normalized = str_replace('\\', '/', ltrim($path, '/'));

        foreach (self::FORBIDDEN_RUNTIME_PATHS as $forbidden) {
            if ($normalized === $forbidden || str_starts_with($normalized, rtrim($forbidden, '/').'/')) {
                return true;
            }
        }

        return false;
    }

    protected function hasPathTraversal(array $plannedWrites): bool
    {
        foreach ($plannedWrites as $write) {
            if (str_contains((string) ($write['source_relative_path'] ?? ''), '..') || str_contains((string) ($write['future_runtime_path'] ?? ''), '..')) {
                return true;
            }
        }

        return false;
    }

    protected function plannedBytes(array $plannedWrites, string $stagingRoot): int
    {
        $bytes = 0;

        foreach ($plannedWrites as $write) {
            $source = $stagingRoot.'/'.str_replace('\\', '/', ltrim((string) ($write['source_relative_path'] ?? ''), '/'));
            if ($this->pathStartsWith($source, $stagingRoot) && is_file(base_path($source))) {
                $bytes += (int) filesize(base_path($source));
            }
        }

        return $bytes;
    }

    protected function overwriteActionsHaveBackups(array $plannedWrites, ?array $backupManifest): bool
    {
        foreach ($plannedWrites as $write) {
            if (($write['write_action'] ?? null) !== 'overwrite' && ($write['backup_required'] ?? false) !== true) {
                continue;
            }

            if (! $this->hasBackupRecord((string) ($write['future_runtime_path'] ?? ''), $backupManifest)) {
                return false;
            }
        }

        return true;
    }

    protected function hasBackupRecord(string $targetPath, ?array $backupManifest): bool
    {
        if (! is_array($backupManifest)) {
            return false;
        }

        foreach (($backupManifest['backups'] ?? []) as $backup) {
            if (($backup['future_runtime_path'] ?? null) === $targetPath && ($backup['backup_created'] ?? false) === true && $this->pathStartsWith((string) ($backup['backup_path'] ?? ''), 'storage/app/builder-publish-backups/')) {
                return true;
            }
        }

        return false;
    }

    protected function migrationsAreFilesOnly(array $plannedWrites): bool
    {
        foreach ($plannedWrites as $write) {
            if (($write['write_action'] ?? null) === 'planned_migration' && ($write['migration_execution_allowed_in_this_phase'] ?? true) !== false) {
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

    protected function booleanConfig(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
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
                'runtime_write_execution_mvp' => true,
                'publish_executed' => false,
                'migrations_run' => false,
                'routes_registered' => false,
                'module_marked_published' => false,
                'rollback_executed' => false,
            ], $payload),
            'created_at' => now(),
        ]);
    }
}
