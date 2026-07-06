<?php

namespace App\Services\Builder;

use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class BuilderRuntimeWriteKillSwitchGuardService
{
    public function check(BuilderPublishExecution $execution): array
    {
        $execution->loadMissing('definition', 'approvalRequest');

        $checks = [];
        $blockers = [];
        $warnings = [];
        $metadata = $execution->metadata_json ?: [];

        $guardRoot = 'storage/app/builder-runtime-write-guards/'.$execution->builder_definition_id.'/'.$execution->getKey();
        $guardReportPath = $guardRoot.'/kill-switch-guard.json';
        $readinessPath = (string) ($metadata['post_backup_runtime_write_readiness_path'] ?? '');
        $readinessReport = $this->readJsonIfAllowed($readinessPath, 'storage/app/builder-runtime-write-readiness/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $runtimeWriteEnabled = $this->booleanConfig(config('builder.runtime_write.enabled', false));
        $maxFiles = (int) config('builder.runtime_write.max_files_per_execution', 25);
        $maxBytes = (int) config('builder.runtime_write.max_total_bytes_per_execution', 5242880);

        $this->logAudit($execution, 'runtime_write_kill_switch_checked', [
            'post_backup_readiness_path' => $readinessPath,
            'runtime_write_enabled' => $runtimeWriteEnabled,
            'max_files_per_execution' => $maxFiles,
            'max_total_bytes_per_execution' => $maxBytes,
        ]);

        $allowedStatuses = [
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_READINESS_PASSED,
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_BLOCKED,
            BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_PASSED,
        ];

        $this->addCheck($checks, 'execution_exists', $execution->exists, true, 'Publish execution record must exist.', $blockers);
        $this->addCheck($checks, 'execution_status_ready_for_guard', in_array($execution->status, $allowedStatuses, true), true, 'Execution status must be runtime_write_readiness_passed or a prior guard status.', $blockers);
        $this->addCheck($checks, 'post_backup_readiness_report_exists', is_array($readinessReport), true, 'Post-backup runtime write readiness report must exist under storage.', $blockers);
        $this->addCheck($checks, 'post_backup_readiness_valid_json', is_array($readinessReport), true, 'Post-backup runtime write readiness report JSON must be valid.', $blockers);
        $this->addCheck($checks, 'post_backup_readiness_ready_true', ($readinessReport['ready_for_runtime_write_execution'] ?? false) === true, true, 'Post-backup readiness must remain ready.', $blockers);
        $this->addCheck($checks, 'config_runtime_write_enabled', $runtimeWriteEnabled, true, 'Runtime write is disabled by the builder.runtime_write.enabled kill-switch.', $blockers);
        $this->addCheck($checks, 'max_files_per_execution_configured', $maxFiles > 0, true, 'Runtime write max files configuration must be greater than zero.', $blockers);
        $this->addCheck($checks, 'max_total_bytes_per_execution_configured', $maxBytes > 0, true, 'Runtime write max bytes configuration must be greater than zero.', $blockers);
        $this->addCheck($checks, 'ai_cannot_override', true, true, 'AI Builder Agent cannot override the kill-switch.', $blockers);
        $this->addCheck($checks, 'mcp_cannot_override', true, true, 'MCP cannot override the kill-switch.', $blockers);
        $this->addCheck($checks, 'no_runtime_write_endpoint', ! $this->routeUriContains('execute-runtime-write'), true, 'No runtime write endpoint may exist.', $blockers);
        $this->addCheck($checks, 'no_copy_to_runtime_endpoint', ! $this->routeUriContains('copy-to-runtime'), true, 'No copy-to-runtime endpoint may exist.', $blockers);
        $this->addCheck($checks, 'no_publish_endpoint', ! $this->hasExecutablePublishRoute(), true, 'No executable publish endpoint may exist.', $blockers);
        $this->addCheck($checks, 'no_rollback_endpoint', ! $this->routeUriContains('rollback-executions'), true, 'No rollback endpoint may exist.', $blockers);
        $this->addCheck($checks, 'runtime_writes_zero', true, true, 'Runtime writes remain zero.', $blockers);
        $this->addCheck($checks, 'publish_executed_false', true, true, 'Publish is not executed.', $blockers);
        $this->addCheck($checks, 'copy_to_runtime_false', true, true, 'Copy to runtime is not executed.', $blockers);

        $passed = $blockers === [];
        $status = $passed
            ? BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_PASSED
            : BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_BLOCKED;

        $report = [
            'execution_id' => $execution->getKey(),
            'status' => $status,
            'runtime_write_enabled' => $runtimeWriteEnabled,
            'runtime_write_guard_passed' => $passed,
            'runtime_write_guard_passed_is_not_execution' => true,
            'safe' => $passed,
            'writes_performed' => 0,
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
            'runtime_module_effect' => 'none',
            'guard_report_path' => $guardReportPath,
            'post_backup_readiness_path' => $readinessPath,
            'config' => [
                'runtime_write_enabled' => $runtimeWriteEnabled,
                'max_files_per_execution' => $maxFiles,
                'max_total_bytes_per_execution' => $maxBytes,
            ],
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
                'override_kill_switch',
            ],
            'next_allowed_actions' => [
                'review kill-switch guard',
                'future operator acknowledgement persistence',
                'future runtime write implementation after separate task',
            ],
        ];

        File::ensureDirectoryExists(base_path($guardRoot));
        File::put(base_path($guardReportPath), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $metadata['runtime_write_kill_switch_guard_path'] = $guardReportPath;
        $metadata['runtime_write_kill_switch_guard_report'] = $report;

        $execution->fill([
            'status' => $status,
            'failure_reason' => $passed ? null : implode('; ', $report['blockers']),
            'metadata_json' => $metadata,
        ])->save();

        $this->logAudit($execution->fresh(), $passed ? 'runtime_write_kill_switch_passed' : 'runtime_write_kill_switch_blocked', [
            'guard_report_path' => $guardReportPath,
            'runtime_write_enabled' => $runtimeWriteEnabled,
            'runtime_write_guard_passed' => $passed,
            'runtime_write_guard_passed_is_not_execution' => true,
            'blockers' => $report['blockers'],
        ]);

        return $report;
    }

    protected function booleanConfig(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
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
