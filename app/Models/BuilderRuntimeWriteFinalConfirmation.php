<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuilderRuntimeWriteFinalConfirmation extends Model
{
    use HasFactory;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_GRANTED = 'granted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_INVALIDATED = 'invalidated';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'uuid',
        'builder_definition_id',
        'builder_publish_execution_id',
        'builder_publish_approval_request_id',
        'status',
        'candidate_id',
        'definition_checksum',
        'runtime_write_plan_path',
        'staged_validation_report_path',
        'candidate_snapshot_path',
        'approved_candidate_preflight_json',
        'runtime_write_plan_json',
        'requested_by_id',
        'decided_by_id',
        'requested_at',
        'decided_at',
        'expires_at',
        'decision_note',
        'invalidation_reason',
        'metadata_json',
    ];

    protected $casts = [
        'builder_definition_id' => 'integer',
        'builder_publish_execution_id' => 'integer',
        'builder_publish_approval_request_id' => 'integer',
        'requested_by_id' => 'integer',
        'decided_by_id' => 'integer',
        'requested_at' => 'datetime',
        'decided_at' => 'datetime',
        'expires_at' => 'datetime',
        'approved_candidate_preflight_json' => 'array',
        'runtime_write_plan_json' => 'array',
        'metadata_json' => 'array',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(BuilderDefinition::class, 'builder_definition_id');
    }

    public function publishExecution(): BelongsTo
    {
        return $this->belongsTo(BuilderPublishExecution::class, 'builder_publish_execution_id');
    }

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(BuilderPublishApprovalRequest::class, 'builder_publish_approval_request_id');
    }

    public function isGranted(): bool
    {
        return $this->status === self::STATUS_GRANTED;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_REQUESTED, self::STATUS_GRANTED], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_REJECTED,
            self::STATUS_REVOKED,
            self::STATUS_INVALIDATED,
            self::STATUS_EXPIRED,
        ], true);
    }

    public function doesRuntimeWrite(): bool
    {
        return false;
    }
}
