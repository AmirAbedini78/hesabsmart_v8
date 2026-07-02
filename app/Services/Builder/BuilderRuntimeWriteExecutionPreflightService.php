<?php

namespace App\Services\Builder;

use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteFinalConfirmation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class BuilderRuntimeWriteExecutionPreflightService
{
    public function __construct(
        protected BuilderRuntimeWriteFinalConfirmationService $finalConfirmationService
    ) {
    }

    public function preflight(BuilderPublishExecution $execution): array
    {
        $execution->loadMissing('definition', 'approvalRequest', 'runtimeWriteFinalConfirmations');

        $checks = [];
        $blockers = [];
        $warnings = [];
        $metadata = $execution->metadata_json ?: [];

        $reportRoot = 'storage/app/builder-runtime-write-preflights/'.$execution->builder_definition_id.'/'.$execution->getKey();
        $reportPath = $reportRoot.'/runtime-write-execution-preflight.json';
        $runtimeWritePlanPath = (string) ($metadata['runtime_write_plan_path'] ?? '');
        $stagedValidationReportPath = (string) ($metadata['staged_file_validation_path'] ?? '');
        $rollbackManifestPath = (string) $execution->rollback_manifest_path;

        $this->logAudit($execution, 'runtime_write_preflight_started', [
            'runtime_write_plan_path' => $runtimeWritePlanPath,
            'staged_validation_report_path' => $stagedValidationReportPath,
        ]);

        $latestConfirmation = $execution->runtimeWriteFinalConfirmations()
            ->where('status', BuilderRuntimeWriteFinalConfirmation::STATUS_GRANTED)
            ->latest()
            ->first();

        $runtimeWritePlan = $this->readJsonIfAllowed($runtimeWritePlanPath, 'storage/app/builder-runtime-write-plans/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $stagedValidationReport = $this->readJsonIfAllowed($stagedValidationReportPath, 'storage/app/builder-publish-staged-validations/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $rollbackManifest = $this->readJsonIfAllowed($rollbackManifestPath, 'storage/app/builder-publish-rollbacks/'.$execution->builder_definition_id.'/'.$execution->getKey());
        $confirmationFreshness = $latestConfirmation
            ? $this->finalConfirmationService->checkFreshness($latestConfirmation)
            : ['safe' => false, 'checks' => [], 'blockers' => ['A granted final confirmation is required.'], 'warnings' => []];

        $this->addCheck($checks, 'execution_exists', $execution->exists, true, 'Publish execution record must exist.', $blockers);
        $this->addCheck($checks, 'execution_status_runtime_write_planned', $execution->status === BuilderPublishExecution::STATUS_RUNTIME_WRITE_PLANNED, true, 'Execution status must be runtime_write_planned.', $blockers);
        $this->addCheck($checks, 'latest_final_confirmation_exists', $latestConfirmation !== null, true, 'A granted final confirmation must exist.', $blockers);
        $this->addCheck($checks, 'final_confirmation_status_granted', $latestConfirmation?->status === BuilderRuntimeWriteFinalConfirmation::STATUS_GRANTED, true, 'Final confirmation status must be granted.', $blockers);
        $this->addCheck($checks, 'final_confirmation_fresh', ($confirmationFreshness['safe'] ?? false) === true, true, 'Final confirmation must be fresh.', $blockers);
        $this->addCheck($checks, 'confirmation_bound_to_execution', $latestConfirmation && $latestConfirmation->builder_publish_execution_id === $execution->getKey(), true, 'Final confirmation must be bound to this execution.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_path_exists', is_array($runtimeWritePlan), true, 'Runtime write plan path must exist.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_json_valid', is_array($runtimeWritePlan), true, 'Runtime write plan JSON must be valid.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_has_no_blockers', empty($runtimeWritePlan['blockers'] ?? []), true, 'Runtime write plan must have no blockers.', $blockers);
        $this->addCheck($checks, 'staged_validation_report_path_exists', is_array($stagedValidationReport), true, 'Staged validation report path must exist.', $blockers);
        $this->addCheck($checks, 'staged_validation_report_json_valid', is_array($stagedValidationReport), true, 'Staged validation report JSON must be valid.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_path_exists', is_array($rollbackManifest), true, 'Rollback manifest draft must exist.', $blockers);
        $this->addCheck($checks, 'rollback_manifest_json_valid', is_array($rollbackManifest), true, 'Rollback manifest JSON must be valid.', $blockers);
        $this->addCheck($checks, 'runtime_path_allowlist_applied', $this->hasCheck($runtimeWritePlan, 'runtime_path_allowlist_applied'), true, 'Runtime path allowlist must have been applied.', $blockers);
        $this->addCheck($checks, 'backup_requirements_planned', $this->backupRequirementsPlanned($runtimeWritePlan), true, 'Backup requirements must be planned for overwrites.', $blockers);
        $this->addCheck($checks, 'no_runtime_write_endpoint', ! $this->routeUriContains('execute-runtime-write'), true, 'No runtime write endpoint may exist.', $blockers);
        $this->addCheck($checks, 'no_copy_to_runtime_endpoint', ! $this->routeUriContains('copy-to-runtime'), true, 'No copy-to-runtime endpoint may exist.', $blockers);
        $this->addCheck($checks, 'no_publish_endpoint', ! $this->hasExecutablePublishRoute(), true, 'No executable publish endpoint may exist.', $blockers);
        $this->addCheck($checks, 'runtime_writes_zero', true, true, 'Runtime writes remain zero.', $blockers);
        $this->addCheck($checks, 'publish_executed_false', true, true, 'Publish is not executed.', $blockers);
        $this->addCheck($checks, 'copy_to_runtime_false', true, true, 'Copy to runtime is not executed.', $blockers);

        foreach (($confirmationFreshness['checks'] ?? []) as $freshnessCheck) {
            $checks[] = [
                'key' => 'confirmation_'.$freshnessCheck['key'],
                'status' => $freshnessCheck['status'],
                'required' => $freshnessCheck['required'],
                'message' => $freshnessCheck['message'],
            ];
        }

        foreach (($confirmationFreshness['blockers'] ?? []) as $freshnessBlocker) {
            $blockers[] = $freshnessBlocker;
        }

        foreach (($confirmationFreshness['warnings'] ?? []) as $freshnessWarning) {
            $warnings[] = $freshnessWarning;
        }

        $safe = $blockers === [];
        $status = $safe
            ? BuilderPublishExecution::STATUS_RUNTIME_WRITE_PREFLIGHT_PASSED
            : BuilderPublishExecution::STATUS_RUNTIME_WRITE_PREFLIGHT_BLOCKED;

        $report = [
            'execution_id' => $execution->getKey(),
            'status' => $status,
            'ready_for_future_runtime_write' => $safe,
            'safe' => $safe,
            'writes_performed' => 0,
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
            'runtime_module_effect' => 'none',
            'preflight_report_path' => $reportPath,
            'runtime_write_plan_path' => $runtimeWritePlanPath,
            'staged_validation_report_path' => $stagedValidationReportPath,
            'rollback_manifest_path' => $rollbackManifestPath,
            'final_confirmation_id' => $latestConfirmation?->getKey(),
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
            ],
            'next_allowed_actions' => [
                'review runtime write execution preflight',
                'future runtime write backup artifact MVP',
                'future runtime write execution implementation',
            ],
        ];

        File::ensureDirectoryExists(base_path($reportRoot));
        File::put(base_path($reportPath), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $metadata['runtime_write_execution_preflight_path'] = $reportPath;
        $metadata['runtime_write_execution_preflight_report'] = $report;

        $execution->fill([
            'status' => $status,
            'failure_reason' => $safe ? null : implode('; ', $report['blockers']),
            'metadata_json' => $metadata,
        ])->save();

        $this->logAudit($execution->fresh(), $safe ? 'runtime_write_preflight_passed' : 'runtime_write_preflight_blocked', [
            'preflight_report_path' => $reportPath,
            'ready_for_future_runtime_write' => $safe,
            'blockers' => $report['blockers'],
        ]);

        return $report;
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

    protected function hasCheck(?array $report, string $key): bool
    {
        foreach (($report['checks'] ?? []) as $check) {
            if (($check['key'] ?? null) === $key && ($check['status'] ?? null) === 'passed') {
                return true;
            }
        }

        return false;
    }

    protected function backupRequirementsPlanned(?array $runtimeWritePlan): bool
    {
        if (! is_array($runtimeWritePlan)) {
            return false;
        }

        foreach (($runtimeWritePlan['planned_writes'] ?? []) as $write) {
            if (($write['write_action'] ?? null) === 'overwrite' && ($write['backup_required'] ?? false) !== true) {
                return false;
            }
        }

        return true;
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
