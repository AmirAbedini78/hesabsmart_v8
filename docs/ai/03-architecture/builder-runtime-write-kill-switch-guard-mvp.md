# Builder Runtime Write Kill-Switch Guard MVP

## Purpose

The runtime write kill-switch guard is a control-plane checkpoint that proves future runtime write execution is disabled by default. It is intentionally report-only. It checks configuration, the latest post-backup readiness artifact, endpoint absence, and AI/MCP restrictions, then writes a guard report under storage.

This is not runtime write, not publish, not copy-to-runtime, and not rollback.

## Configuration

The guard reads `builder.runtime_write.enabled`, backed by `BUILDER_RUNTIME_WRITE_ENABLED`. The default is `false` when the environment variable is absent. The guard also records `builder.runtime_write.max_files_per_execution` and `builder.runtime_write.max_total_bytes_per_execution` so future runtime write implementations have bounded limits before they exist.

The `.env` file is not edited by this MVP.

## Guard Artifact

Reports are written only to:

`storage/app/builder-runtime-write-guards/{definition_id}/{execution_id}/kill-switch-guard.json`

The report includes:

- `runtime_write_enabled`
- `runtime_write_guard_passed`
- `runtime_write_guard_passed_is_not_execution`
- `runtime_writes_performed: 0`
- `publish_executed: false`
- `copy_to_runtime_executed: false`
- checks, blockers, warnings, forbidden actions, and next allowed actions

With default configuration, the normal status is `runtime_write_guard_blocked`.

## Safety Boundaries

The guard may update only control-plane metadata and audit logs, and may write only the storage report. It must not write runtime module files, copy staged artifacts, run migrations, register routes, mark modules as published, execute rollback, or expose an override.

If a future environment enables the config flag, the guard may report `runtime_write_guard_passed`, but that still is not runtime write execution.

## AI and MCP

AI Builder Agent may summarize a human-initiated guard report. It may not autonomously run the guard, override the kill-switch, treat a passed guard as execution, copy files, publish, or execute runtime write. MCP must not expose kill-switch override or runtime write execution tools.

## Next Steps

Future work may add operator runbook acknowledgement persistence and, only after a separate implementation task, a runtime write execution phase guarded by explicit human command, final confirmation, kill-switch, backups, rollback manifest, and smoke checks.
