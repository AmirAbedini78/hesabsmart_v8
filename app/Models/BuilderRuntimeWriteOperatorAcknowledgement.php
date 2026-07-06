<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuilderRuntimeWriteOperatorAcknowledgement extends Model
{
    use HasFactory;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_INVALIDATED = 'invalidated';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'uuid',
        'builder_definition_id',
        'builder_publish_execution_id',
        'status',
        'definition_checksum',
        'runtime_write_plan_path',
        'post_backup_readiness_path',
        'kill_switch_guard_path',
        'backup_manifest_path',
        'rollback_manifest_path',
        'runbook_version',
        'checklist_json',
        'acknowledged_by_id',
        'acknowledged_at',
        'expires_at',
        'acknowledgement_note',
        'invalidation_reason',
        'metadata_json',
    ];

    protected $casts = [
        'builder_definition_id' => 'integer',
        'builder_publish_execution_id' => 'integer',
        'acknowledged_by_id' => 'integer',
        'acknowledged_at' => 'datetime',
        'expires_at' => 'datetime',
        'checklist_json' => 'array',
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

    public function isAcknowledged(): bool
    {
        return $this->status === self::STATUS_ACKNOWLEDGED;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_REQUESTED, self::STATUS_ACKNOWLEDGED], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
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
