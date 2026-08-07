<?php

namespace App\Services\Builder;

use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BuilderRuntimeWritePostWriteSmokeService
{
    protected const FORBIDDEN_RUNTIME_PATHS = [
        'modules/Core',
        'modules/SaaS',
        'modules/Updater',
        'modules/Installer',
        'public/build',
        'vendor',
        'node_modules',
        'resources/js/app.js',
        'routes/web.php',
    ];

    protected const ALLOWED_GENERATED_MODULE_PATH_SEGMENTS = [
        'App/Models',
        'App/Http/Controllers',
        'App/Http/Resources',
        'database/migrations',
        'resources/js',
        'routes',
    ];

    public function verify(BuilderPublishExecution $execution): array
    {
        $execution->loadMissing('definition');
        $this->logAudit($execution, 'runtime_write_smoke_started');

        $checks = [];
        $blockers = [];
        $metadata = $execution->metadata_json ?: [];
        $runtimeWriteReportPath = (string) ($metadata['runtime_write_report_path'] ?? '');
        $runtimeWriteReport = $this->readJsonIfAllowed(
            $runtimeWriteReportPath,
            'storage/app/builder-runtime-write-executions/'.$execution->builder_definition_id.'/'.$execution->getKey()
        );
        $rollbackManifest = $this->readJsonIfAllowed(
            (string) $execution->rollback_manifest_path,
            'storage/app/builder-publish-rollbacks/'.$execution->builder_definition_id.'/'.$execution->getKey()
        );
        $committedFiles = is_array($runtimeWriteReport) ? ($runtimeWriteReport['files_committed'] ?? []) : [];

        $this->addCheck($checks, 'execution_status_runtime_write_succeeded', $execution->status === BuilderPublishExecution::STATUS_RUNTIME_WRITE_SUCCEEDED, 'Execution status must be runtime_write_succeeded.', $blockers);
        $this->addCheck($checks, 'runtime_write_report_exists', $this->fileExistsIfAllowed($runtimeWriteReportPath, 'storage/app/builder-runtime-write-executions/'.$execution->builder_definition_id.'/'.$execution->getKey()), 'Runtime write report must exist.', $blockers);
        $this->addCheck($checks, 'runtime_write_report_valid_json', is_array($runtimeWriteReport), 'Runtime write report must be valid JSON.', $blockers);
        $this->addCheck($checks, 'runtime_write_executed_true', ($runtimeWriteReport['runtime_write_executed'] ?? false) === true, 'Runtime write report must confirm guarded runtime write execution.', $blockers);
        $this->addCheck($checks, 'runtime_write_report_publish_false', ($runtimeWriteReport['publish_executed'] ?? true) === false, 'Runtime write report must confirm publish was not executed.', $blockers);
        $this->addCheck($checks, 'runtime_write_report_migrations_false', ($runtimeWriteReport['migrations_run'] ?? true) === false, 'Runtime write report must confirm migrations were not run.', $blockers);
        $this->addCheck($checks, 'runtime_write_report_routes_false', ($runtimeWriteReport['routes_registered'] ?? true) === false, 'Runtime write report must confirm routes were not registered.', $blockers);
        $this->addCheck($checks, 'runtime_write_report_mark_published_false', ($runtimeWriteReport['module_marked_published'] ?? true) === false, 'Runtime write report must confirm the module was not marked published.', $blockers);
        $this->addCheck($checks, 'runtime_write_report_rollback_false', ($runtimeWriteReport['rollback_executed'] ?? true) === false, 'Runtime write report must confirm rollback was not executed.', $blockers);
        $this->addCheck($checks, 'committed_files_present', is_array($committedFiles) && $committedFiles !== [], 'Runtime write report must contain committed files.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_exists', $this->fileExistsIfAllowed((string) $execution->rollback_manifest_path, 'storage/app/builder-publish-rollbacks/'.$execution->builder_definition_id.'/'.$execution->getKey()), 'Rollback manifest must exist.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_valid_json', is_array($rollbackManifest), 'Rollback manifest must be valid JSON.', $blockers);
        $this->addCheck($checks, 'builder_definition_not_published', $execution->definition !== null && $execution->definition->status !== 'published', 'BuilderDefinition must not be published.', $blockers);

        if ($blockers !== []) {
            return $this->finish($execution, BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_BLOCKED, [], $checks, $blockers);
        }

        $files = [];
        $failures = [];
        foreach ($committedFiles as $committedFile) {
            $files[] = $this->verifyCommittedFile($execution, $committedFile, $rollbackManifest, $metadata, $failures);
        }

        foreach ([
            'every_runtime_path_allowlisted' => [$this->every($files, 'allowlisted'), 'Every committed runtime path must remain allowlisted.'],
            'no_forbidden_runtime_paths' => [! $this->any($files, 'forbidden_path'), 'No committed runtime path may use a forbidden scope.'],
            'every_committed_file_exists' => [$this->every($files, 'exists'), 'Every committed runtime file must exist.'],
            'every_committed_hash_matches' => [$this->every($files, 'hash_matches'), 'Every committed runtime file hash must match the runtime write report.'],
            'php_syntax_valid' => [$this->everyApplicable($files, 'syntax_checked', 'syntax_valid'), 'Every generated PHP file must pass syntax validation without execution.'],
            'json_files_valid' => [$this->everyApplicable($files, 'json_checked', 'json_valid'), 'Every committed JSON file must contain valid JSON.'],
            'migration_files_not_executed' => [$this->every($files, 'migration_not_executed'), 'Generated migration files are verified as files only and are not executed.'],
            'rollback_entries_complete' => [$this->every($files, 'rollback_manifest_entry_exists'), 'Rollback manifest must contain every committed file entry.'],
            'backup_references_complete_for_overwrites' => [$this->every($files, 'backup_reference_exists_when_required'), 'Overwrite files must retain backup evidence under storage.'],
            'smoke_runtime_writes_zero' => [true, 'Post-write smoke performs zero runtime writes.'],
            'smoke_publish_false' => [true, 'Post-write smoke does not publish.'],
            'smoke_migrations_false' => [true, 'Post-write smoke does not run migrations.'],
            'smoke_routes_false' => [true, 'Post-write smoke does not register routes.'],
            'smoke_mark_published_false' => [true, 'Post-write smoke does not mark modules published.'],
            'smoke_rollback_false' => [true, 'Post-write smoke does not execute rollback.'],
        ] as $key => [$passed, $message]) {
            $this->addCheck($checks, $key, $passed, $message, $failures);
        }

        return $this->finish(
            $execution,
            $this->any($files, 'forbidden_path')
                ? BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_BLOCKED
                : ($failures === [] ? BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_PASSED : BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_FAILED),
            $files,
            $checks,
            $failures
        );
    }

    protected function verifyCommittedFile(BuilderPublishExecution $execution, array $committedFile, array $rollbackManifest, array $metadata, array &$failures): array
    {
        $runtimePath = str_replace('\\', '/', ltrim((string) ($committedFile['future_runtime_path'] ?? ''), '/'));
        $fullPath = base_path($runtimePath);
        $expectedSha = (string) ($committedFile['committed_sha256'] ?? '');
        $pathAllowlisted = $this->runtimePathAllowed($runtimePath);
        $exists = $pathAllowlisted && is_file($fullPath);
        $allowlisted = $pathAllowlisted && (! $exists || $this->resolvedPathIsSafe($fullPath, $runtimePath));
        $forbidden = $this->matchesForbiddenRuntimePath($runtimePath) || str_contains($runtimePath, '..') || ! $allowlisted;
        $actualSha = $exists && $allowlisted ? (string) hash_file('sha256', $fullPath) : '';
        $hashMatches = $exists && $expectedSha !== '' && hash_equals($expectedSha, $actualSha);
        $extension = strtolower((string) pathinfo($runtimePath, PATHINFO_EXTENSION));
        $syntaxChecked = $extension === 'php' && $exists && $allowlisted;
        $syntaxValid = null;
        $syntaxOutput = [];

        if ($syntaxChecked) {
            $exitCode = 1;
            exec(PHP_BINARY.' -l '.escapeshellarg($fullPath).' 2>&1', $syntaxOutput, $exitCode);
            $syntaxValid = $exitCode === 0;
        }

        $jsonChecked = $extension === 'json' && $exists && $allowlisted;
        $jsonValid = null;
        if ($jsonChecked) {
            json_decode((string) file_get_contents($fullPath), true);
            $jsonValid = json_last_error() === JSON_ERROR_NONE;
        }

        $readableContentValid = ! in_array($extension, ['js', 'vue', 'css'], true)
            || ($exists && is_readable($fullPath) && filesize($fullPath) > 0);
        $migrationFile = preg_match('#^modules/[^/]+/database/migrations/#', $runtimePath) === 1;
        $rollbackEntry = $this->findPathEntry($rollbackManifest['committed_file_entries'] ?? [], $runtimePath);
        $writeAction = (string) ($committedFile['write_action'] ?? 'create');
        $backupReference = $writeAction !== 'overwrite' || $this->backupReferenceExists($execution, $runtimePath, $metadata);

        $result = [
            'runtime_path' => $runtimePath,
            'exists' => $exists,
            'allowlisted' => $allowlisted,
            'forbidden_path' => $forbidden,
            'expected_sha256' => $expectedSha,
            'actual_sha256' => $actualSha,
            'hash_matches' => $hashMatches,
            'syntax_checked' => $syntaxChecked,
            'syntax_valid' => $syntaxValid,
            'syntax_output' => $syntaxChecked ? implode("\n", $syntaxOutput) : null,
            'json_checked' => $jsonChecked,
            'json_valid' => $jsonValid,
            'readable_content_valid' => $readableContentValid,
            'migration_file' => $migrationFile,
            'migration_executed' => false,
            'migration_not_executed' => true,
            'rollback_manifest_entry_exists' => $rollbackEntry !== null,
            'backup_reference_exists_when_required' => $backupReference,
            'runtime_file_modified_by_smoke' => false,
        ];

        foreach ([
            'exists' => "Committed file is missing: {$runtimePath}",
            'allowlisted' => "Committed path is not allowlisted: {$runtimePath}",
            'hash_matches' => "Committed file hash mismatch: {$runtimePath}",
            'readable_content_valid' => "Committed frontend file is empty or unreadable: {$runtimePath}",
            'rollback_manifest_entry_exists' => "Rollback manifest is missing committed entry: {$runtimePath}",
            'backup_reference_exists_when_required' => "Overwrite file is missing backup evidence: {$runtimePath}",
        ] as $key => $message) {
            if ($result[$key] !== true) {
                $failures[] = $message;
            }
        }
        if ($syntaxChecked && $syntaxValid !== true) {
            $failures[] = "PHP syntax validation failed: {$runtimePath}";
        }
        if ($jsonChecked && $jsonValid !== true) {
            $failures[] = "JSON validation failed: {$runtimePath}";
        }

        return $result;
    }

    protected function finish(BuilderPublishExecution $execution, string $status, array $files, array $checks, array $blockers): array
    {
        $reportRoot = 'storage/app/builder-runtime-write-smoke/'.$execution->builder_definition_id.'/'.$execution->getKey();
        $reportPath = $reportRoot.'/post-write-smoke.json';
        $passed = $status === BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_PASSED;
        $report = [
            'execution_id' => $execution->getKey(),
            'status' => $status,
            'post_write_smoke_passed' => $passed,
            'post_write_smoke_is_not_publish' => true,
            'safe' => $blockers === [],
            'runtime_files_modified_by_smoke' => 0,
            'runtime_writes_performed_by_smoke' => 0,
            'publish_executed' => false,
            'migrations_run' => false,
            'routes_registered' => false,
            'module_marked_published' => false,
            'rollback_executed' => false,
            'smoke_report_path' => $reportPath,
            'files' => $files,
            'summary' => $this->summarize($files),
            'checks' => $checks,
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => [],
            'forbidden_actions' => [
                'publish',
                'run_migrations',
                'register_routes',
                'mark_module_published',
                'execute_rollback',
                'modify_runtime_files',
                'write_core_saas_updater_installer',
                'write_public_build',
                'write_vendor',
                'write_node_modules',
            ],
            'next_allowed_actions' => [
                'review post-write smoke report',
                'future module registration planning after separate task',
            ],
        ];

        File::ensureDirectoryExists(base_path($reportRoot));
        File::put(base_path($reportPath), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $metadata = $execution->metadata_json ?: [];
        $metadata['runtime_write_post_write_smoke_path'] = $reportPath;
        $metadata['runtime_write_post_write_smoke_report'] = $report;
        $execution->fill([
            'status' => $status,
            'failure_reason' => $passed ? null : implode('; ', $report['blockers']),
            'metadata_json' => $metadata,
        ])->save();

        $eventType = match ($status) {
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_PASSED => 'runtime_write_smoke_passed',
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_FAILED => 'runtime_write_smoke_failed',
            default => 'runtime_write_smoke_blocked',
        };
        $this->logAudit($execution->fresh(), $eventType, [
            'smoke_report_path' => $reportPath,
            'post_write_smoke_passed' => $passed,
            'verified_files' => count($files),
        ]);

        return $report;
    }

    protected function summarize(array $files): array
    {
        return [
            'total_files' => count($files),
            'existing_files' => count(array_filter($files, fn (array $file) => $file['exists'] === true)),
            'hash_matches' => count(array_filter($files, fn (array $file) => $file['hash_matches'] === true)),
            'php_syntax_passed' => count(array_filter($files, fn (array $file) => $file['syntax_checked'] === true && $file['syntax_valid'] === true)),
            'json_valid' => count(array_filter($files, fn (array $file) => $file['json_checked'] === true && $file['json_valid'] === true)),
            'migration_files_not_executed' => count(array_filter($files, fn (array $file) => $file['migration_file'] === true && $file['migration_executed'] === false)),
            'rollback_entries_found' => count(array_filter($files, fn (array $file) => $file['rollback_manifest_entry_exists'] === true)),
            'failed' => count(array_filter($files, fn (array $file) => ! $this->filePassed($file))),
        ];
    }

    protected function filePassed(array $file): bool
    {
        return $file['exists'] === true
            && $file['allowlisted'] === true
            && $file['hash_matches'] === true
            && $file['readable_content_valid'] === true
            && ($file['syntax_checked'] !== true || $file['syntax_valid'] === true)
            && ($file['json_checked'] !== true || $file['json_valid'] === true)
            && $file['rollback_manifest_entry_exists'] === true
            && $file['backup_reference_exists_when_required'] === true;
    }

    protected function runtimePathAllowed(string $path): bool
    {
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1 || $this->matchesForbiddenRuntimePath($path)) {
            return false;
        }
        if (! preg_match('#^modules/([^/]+)/(.+)$#', $path, $matches) || in_array($matches[1], ['Core', 'SaaS', 'Updater', 'Installer'], true)) {
            return false;
        }
        foreach (self::ALLOWED_GENERATED_MODULE_PATH_SEGMENTS as $segment) {
            if ($matches[2] === $segment || str_starts_with($matches[2], $segment.'/')) {
                return true;
            }
        }

        return false;
    }

    protected function resolvedPathIsSafe(string $fullPath, string $runtimePath): bool
    {
        if (is_link($fullPath) || ! is_file($fullPath)) {
            return false;
        }
        $resolved = realpath($fullPath);
        if ($resolved === false || ! preg_match('#^modules/([^/]+)/#', $runtimePath, $matches)) {
            return false;
        }
        $moduleRoot = realpath(base_path('modules/'.$matches[1]));

        return $moduleRoot !== false && ($resolved === $moduleRoot || str_starts_with($resolved, rtrim($moduleRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR));
    }

    protected function matchesForbiddenRuntimePath(string $path): bool
    {
        $normalized = str_replace('\\', '/', ltrim($path, '/'));
        foreach (self::FORBIDDEN_RUNTIME_PATHS as $forbidden) {
            if ($normalized === $forbidden || str_starts_with($normalized, $forbidden.'/')) {
                return true;
            }
        }

        return false;
    }

    protected function backupReferenceExists(BuilderPublishExecution $execution, string $runtimePath, array $metadata): bool
    {
        $backupRoot = 'storage/app/builder-publish-backups/'.$execution->builder_definition_id.'/'.$execution->getKey();
        $manifest = $this->readJsonIfAllowed((string) ($metadata['runtime_write_backup_manifest_path'] ?? ''), $backupRoot);
        if (! is_array($manifest)) {
            return false;
        }
        foreach (($manifest['backups'] ?? []) as $backup) {
            $backupPath = (string) ($backup['backup_path'] ?? '');
            if (($backup['future_runtime_path'] ?? null) === $runtimePath
                && ($backup['backup_created'] ?? false) === true
                && $this->fileExistsIfAllowed($backupPath, $backupRoot)) {
                return true;
            }
        }

        return false;
    }

    protected function findPathEntry(array $entries, string $runtimePath): ?array
    {
        foreach ($entries as $entry) {
            if (($entry['future_runtime_path'] ?? null) === $runtimePath) {
                return $entry;
            }
        }

        return null;
    }

    protected function readJsonIfAllowed(string $path, string $prefix): ?array
    {
        if (! $this->fileExistsIfAllowed($path, $prefix)) {
            return null;
        }
        $fullPath = base_path($path);
        $decoded = json_decode((string) file_get_contents($fullPath), true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function fileExistsIfAllowed(string $path, string $prefix): bool
    {
        if (! $this->pathStartsWith($path, $prefix) || str_contains($path, '..')) {
            return false;
        }
        $fullPath = base_path($path);
        if (! is_file($fullPath) || is_link($fullPath)) {
            return false;
        }
        $resolved = realpath($fullPath);
        $resolvedPrefix = realpath(base_path($prefix));

        return $resolved !== false
            && $resolvedPrefix !== false
            && ($resolved === $resolvedPrefix || str_starts_with($resolved, rtrim($resolvedPrefix, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR));
    }

    protected function pathStartsWith(string $path, string $prefix): bool
    {
        $normalized = str_replace('\\', '/', ltrim($path, '/'));

        return $normalized === rtrim($prefix, '/') || str_starts_with($normalized, rtrim($prefix, '/').'/');
    }

    protected function every(array $files, string $key): bool
    {
        return $files !== [] && array_reduce($files, fn (bool $carry, array $file) => $carry && ($file[$key] ?? false) === true, true);
    }

    protected function any(array $files, string $key): bool
    {
        return array_reduce($files, fn (bool $carry, array $file) => $carry || ($file[$key] ?? false) === true, false);
    }

    protected function everyApplicable(array $files, string $appliesKey, string $resultKey): bool
    {
        foreach ($files as $file) {
            if (($file[$appliesKey] ?? false) === true && ($file[$resultKey] ?? false) !== true) {
                return false;
            }
        }

        return true;
    }

    protected function addCheck(array &$checks, string $key, bool $passed, string $message, array &$blockers): void
    {
        $checks[] = ['key' => $key, 'status' => $passed ? 'passed' : 'blocked', 'required' => true, 'message' => $message];
        if (! $passed) {
            $blockers[] = $message;
        }
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
                'runtime_write_post_write_smoke_mvp' => true,
                'runtime_files_modified_by_smoke' => 0,
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
