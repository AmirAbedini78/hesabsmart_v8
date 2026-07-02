<?php

namespace App\Services\Builder;

use App\Models\BuilderPublishApprovalRequest;
use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteFinalConfirmation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class BuilderRuntimeWriteFinalConfirmationService
{
    public function listForExecution(BuilderPublishExecution $execution): Collection
    {
        return $execution->runtimeWriteFinalConfirmations()
            ->latest()
            ->get();
    }

    public function request(BuilderPublishExecution $execution, ?array $metadata = []): array
    {
        $execution->loadMissing('definition', 'approvalRequest');
        $runtimeWritePlanPath = (string) data_get($execution->metadata_json, 'runtime_write_plan_path');
        $runtimeWritePlan = $this->readRuntimeWritePlan($runtimeWritePlanPath);

        if ($execution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_PLANNED) {
            throw new RuntimeException('Runtime write final confirmation requires execution status runtime_write_planned.');
        }

        if (! is_array($runtimeWritePlan)) {
            throw new RuntimeException('Runtime write plan artifact is missing or invalid.');
        }

        $confirmation = BuilderRuntimeWriteFinalConfirmation::create([
            'uuid' => (string) Str::uuid(),
            'builder_definition_id' => $execution->builder_definition_id,
            'builder_publish_execution_id' => $execution->getKey(),
            'builder_publish_approval_request_id' => $execution->builder_publish_approval_request_id,
            'status' => BuilderRuntimeWriteFinalConfirmation::STATUS_REQUESTED,
            'candidate_id' => $execution->candidate_id,
            'definition_checksum' => $execution->definition_checksum,
            'runtime_write_plan_path' => $runtimeWritePlanPath,
            'staged_validation_report_path' => (string) data_get($execution->metadata_json, 'staged_file_validation_path'),
            'candidate_snapshot_path' => $execution->candidate_snapshot_path,
            'approved_candidate_preflight_json' => $execution->preflight_report_json,
            'runtime_write_plan_json' => $runtimeWritePlan,
            'requested_by_id' => auth()->id(),
            'requested_at' => now(),
            'metadata_json' => $metadata ?: [],
        ]);

        $this->logAudit($confirmation, 'runtime_write_confirmation_requested', [
            'runtime_write_plan_path' => $runtimeWritePlanPath,
        ]);

        return $this->report($confirmation->fresh());
    }

    public function grant(BuilderRuntimeWriteFinalConfirmation $confirmation, ?string $note = null): array
    {
        $freshness = $this->checkFreshness($confirmation);

        if (($freshness['safe'] ?? false) !== true) {
            $confirmation->fill([
                'status' => BuilderRuntimeWriteFinalConfirmation::STATUS_INVALIDATED,
                'decided_by_id' => auth()->id(),
                'decided_at' => now(),
                'decision_note' => $note,
                'invalidation_reason' => implode('; ', $freshness['blockers'] ?? []),
            ])->save();

            $this->logAudit($confirmation->fresh(), 'runtime_write_confirmation_invalidated', [
                'blockers' => $freshness['blockers'] ?? [],
            ]);

            return $this->report($confirmation->fresh(), $freshness['checks'] ?? [], $freshness['blockers'] ?? [], $freshness['warnings'] ?? []);
        }

        if ($confirmation->status !== BuilderRuntimeWriteFinalConfirmation::STATUS_REQUESTED) {
            throw new RuntimeException('Only requested final confirmations can be granted.');
        }

        $confirmation->fill([
            'status' => BuilderRuntimeWriteFinalConfirmation::STATUS_GRANTED,
            'decided_by_id' => auth()->id(),
            'decided_at' => now(),
            'decision_note' => $note,
        ])->save();

        $this->logAudit($confirmation->fresh(), 'runtime_write_confirmation_granted');

        return $this->report($confirmation->fresh(), $freshness['checks'] ?? []);
    }

    public function reject(BuilderRuntimeWriteFinalConfirmation $confirmation, ?string $note = null): array
    {
        if ($confirmation->status !== BuilderRuntimeWriteFinalConfirmation::STATUS_REQUESTED) {
            throw new RuntimeException('Only requested final confirmations can be rejected.');
        }

        $confirmation->fill([
            'status' => BuilderRuntimeWriteFinalConfirmation::STATUS_REJECTED,
            'decided_by_id' => auth()->id(),
            'decided_at' => now(),
            'decision_note' => $note,
        ])->save();

        $this->logAudit($confirmation->fresh(), 'runtime_write_confirmation_rejected');

        return $this->report($confirmation->fresh());
    }

    public function revoke(BuilderRuntimeWriteFinalConfirmation $confirmation, ?string $note = null): array
    {
        if (! in_array($confirmation->status, [
            BuilderRuntimeWriteFinalConfirmation::STATUS_REQUESTED,
            BuilderRuntimeWriteFinalConfirmation::STATUS_GRANTED,
        ], true)) {
            throw new RuntimeException('Only requested or granted final confirmations can be revoked.');
        }

        $confirmation->fill([
            'status' => BuilderRuntimeWriteFinalConfirmation::STATUS_REVOKED,
            'decided_by_id' => auth()->id(),
            'decided_at' => now(),
            'decision_note' => $note,
        ])->save();

        $this->logAudit($confirmation->fresh(), 'runtime_write_confirmation_revoked');

        return $this->report($confirmation->fresh());
    }

    public function checkFreshness(BuilderRuntimeWriteFinalConfirmation $confirmation): array
    {
        $confirmation->loadMissing('definition', 'publishExecution', 'approvalRequest');
        $checks = [];
        $blockers = [];
        $warnings = [];
        $execution = $confirmation->publishExecution;
        $definition = $confirmation->definition;
        $approval = $confirmation->approvalRequest;

        $this->addCheck($checks, 'execution_exists', $execution !== null, true, 'Publish execution record must exist.', $blockers);
        $this->addCheck($checks, 'execution_status_runtime_write_planned', $execution?->status === BuilderPublishExecution::STATUS_RUNTIME_WRITE_PLANNED, true, 'Execution status must remain runtime_write_planned.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_path_unchanged', $execution && $confirmation->runtime_write_plan_path === (string) data_get($execution->metadata_json, 'runtime_write_plan_path'), true, 'Runtime write plan path must be unchanged.', $blockers);
        $runtimeWritePlan = $this->readRuntimeWritePlan((string) $confirmation->runtime_write_plan_path);
        $this->addCheck($checks, 'runtime_write_plan_file_exists', is_array($runtimeWritePlan), true, 'Runtime write plan file must exist and be valid JSON.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_json_valid', is_array($runtimeWritePlan), true, 'Runtime write plan JSON must be valid.', $blockers);
        $this->addCheck($checks, 'definition_checksum_unchanged', $definition && $confirmation->definition_checksum === $definition->checksum, true, 'Definition checksum must be unchanged.', $blockers);
        $this->addCheck($checks, 'approval_request_still_approved', $approval === null || $approval->status === BuilderPublishApprovalRequest::STATUS_APPROVED, true, 'Linked approval request must remain approved.', $blockers);
        $this->addCheck($checks, 'staged_validation_report_path_exists', $this->storageJsonExists((string) $confirmation->staged_validation_report_path, 'storage/app/builder-publish-staged-validations/'), true, 'Staged validation report path must still exist.', $blockers);
        $this->addCheck($checks, 'candidate_id_unchanged', ! $execution || ! filled($confirmation->candidate_id) || $confirmation->candidate_id === $execution->candidate_id, true, 'Candidate id must be unchanged.', $blockers);
        $this->addCheck($checks, 'runtime_write_plan_has_no_blockers', empty($runtimeWritePlan['blockers'] ?? []), true, 'Runtime write plan must not have blockers.', $blockers);
        $this->addCheck($checks, 'confirmation_not_expired', $confirmation->expires_at === null || $confirmation->expires_at->isFuture(), true, 'Final confirmation must not be expired.', $blockers);
        $this->addCheck($checks, 'confirmation_does_not_publish', true, true, 'Final confirmation does not publish.', $blockers);
        $this->addCheck($checks, 'confirmation_does_not_write_runtime', true, true, 'Final confirmation does not write runtime files.', $blockers);

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
        BuilderRuntimeWriteFinalConfirmation $confirmation,
        array $checks = [],
        array $blockers = [],
        array $warnings = []
    ): array {
        return [
            'confirmation_id' => $confirmation->getKey(),
            'status' => $confirmation->status,
            'safe' => $blockers === [],
            'runtime_writes_performed' => 0,
            'publish_executed' => false,
            'copy_to_runtime_executed' => false,
            'confirmation_does_not_publish' => true,
            'confirmation_does_not_write_runtime' => true,
            'confirmation' => $confirmation->fresh(),
            'checks' => $checks,
            'blockers' => array_values(array_unique($blockers)),
            'warnings' => array_values(array_unique($warnings)),
            'forbidden_actions' => [
                'execute_runtime_write',
                'copy_to_runtime',
                'publish',
                'run_migrations',
                'register_routes',
            ],
            'next_allowed_actions' => [
                'review confirmation state',
                'future runtime write implementation after separate task',
            ],
        ];
    }

    protected function addCheck(array &$checks, string $key, bool $passed, bool $required, string $message, array &$blockers): void
    {
        $status = $passed ? 'passed' : ($required ? 'blocked' : 'warning');
        $checks[] = compact('key', 'status', 'required', 'message');

        if (! $passed && $required) {
            $blockers[] = $message;
        }
    }

    protected function readRuntimeWritePlan(string $path): ?array
    {
        if (! $this->storageJsonExists($path, 'storage/app/builder-runtime-write-plans/')) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents(base_path($path)), true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function storageJsonExists(string $path, string $prefix): bool
    {
        $normalized = str_replace('\\', '/', ltrim($path, '/'));

        return ($normalized === rtrim($prefix, '/') || str_starts_with($normalized, rtrim($prefix, '/').'/'))
            && is_file(base_path($normalized));
    }

    protected function logAudit(BuilderRuntimeWriteFinalConfirmation $confirmation, string $eventType, array $payload = []): BuilderPublishAuditLog
    {
        return BuilderPublishAuditLog::create([
            'uuid' => (string) Str::uuid(),
            'builder_definition_id' => $confirmation->builder_definition_id,
            'builder_publish_approval_request_id' => $confirmation->builder_publish_approval_request_id,
            'candidate_id' => $confirmation->candidate_id,
            'definition_checksum' => $confirmation->definition_checksum,
            'event_type' => $eventType,
            'actor_id' => auth()->id(),
            'payload_json' => array_merge([
                'builder_runtime_write_final_confirmation_id' => $confirmation->getKey(),
                'builder_publish_execution_id' => $confirmation->builder_publish_execution_id,
                'control_plane_only' => true,
                'runtime_writes_performed' => 0,
                'publish_executed' => false,
                'copy_to_runtime_executed' => false,
            ], $payload),
            'created_at' => now(),
        ]);
    }
}
