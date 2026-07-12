# ERPSMART Builder Runtime Write Execution Final Implementation Plan

## Purpose

This document defines the final implementation plan for a future Builder runtime write execution MVP. It is a planning artifact only. It does not implement runtime write execution, publish execution, copy-to-runtime behavior, rollback execution, migration execution, route registration, or any runtime module creation.

The current system has a complete pre-runtime-write safety chain:

- publish execution record
- staged file validation
- runtime write plan artifact
- runtime write final confirmation persistence
- runtime write execution preflight
- runtime write backup artifact
- post-backup runtime write readiness
- runtime write kill-switch guard
- runtime write operator acknowledgement persistence
- runtime write architecture review and runbook

Runtime writes remain forbidden until a separate implementation task adds the execution service, endpoint, UI action, verifier, and operational controls.

## Future Implementation Scope

The future runtime write execution MVP may add:

- Future service: `app/Services/Builder/BuilderRuntimeWriteExecutionService.php`
- Future controller method: `BuilderPublishExecutionController::executeRuntimeWrite`
- Future endpoint: `POST /api/builder/publish-executions/{execution}/execute-runtime-write`
- Future UI action label: `Execute Runtime Write`

The future UI action must remain absent until the separate runtime write execution implementation task. It must not be introduced by this planning batch.

## Required Future Execution Status

The future runtime write execution service must only run from:

- `runtime_write_operator_acknowledged`

The operator acknowledgement must be fresh and must still bind to the current post-backup readiness report, kill-switch guard report, definition checksum, runtime write plan path, backup manifest path, and rollback manifest path.

## Future Resulting Statuses

The future runtime write execution MVP may use these execution statuses:

- `runtime_write_started`
- `runtime_write_succeeded`
- `runtime_write_failed`
- `runtime_write_aborted`

It must not set a final published state. Published state requires a separate post-write smoke and publish/finalization task.

## Future Write Behavior

Runtime write execution may only copy validated staged artifacts to allowlisted runtime paths. The implementation must:

1. Re-check post-backup readiness.
2. Re-check kill-switch guard status.
3. Re-check operator acknowledgement freshness.
4. Acquire the runtime write lock.
5. Re-check the runtime path allowlist.
6. Re-check the runtime write plan, backup manifest, and rollback manifest hashes.
7. For each planned file, create a per-file temporary write path.
8. Copy the staged artifact to the temporary path.
9. Verify the temporary file checksum before commit.
10. Use atomic rename where the filesystem supports it.
11. Record committed runtime file hashes.
12. Update the rollback manifest with committed file entries.
13. Write a runtime write report under `storage/app/builder-runtime-write-executions`.
14. Update execution metadata with `runtime_write_report_path`.

The future service must keep each file write auditable and individually attributable to a staged artifact checksum.

## Future Allowed Write Scopes

Future runtime write execution may write only to generated-module scopes approved by the runtime path allowlist:

- `modules/{GeneratedModule}`
- `modules/{GeneratedModule}/App/Models`
- `modules/{GeneratedModule}/App/Http/Controllers`
- `modules/{GeneratedModule}/App/Http/Resources`
- `modules/{GeneratedModule}/resources/js`
- `modules/{GeneratedModule}/routes`
- `modules/{GeneratedModule}/database/migrations` for generated module migration files only

Migration files may be written as files, but migrations must not be executed in the runtime write phase.

## Future Forbidden Runtime Write Behavior

The future runtime write execution service must not:

- run generated migrations
- register routes dynamically
- mark a module as published
- execute rollback
- write `public/build`
- write `vendor`
- write `node_modules`
- write `modules/Core`
- write `modules/SaaS`
- write `modules/Updater`
- write `modules/Installer`
- write `routes/web.php`
- write `resources/js/app.js`
- override the kill-switch
- skip backups
- skip operator acknowledgement
- treat runtime write as publish

## Future Audit Events

The future implementation must write append-only audit events for:

- `runtime_write_execute_requested`
- `runtime_write_started`
- `runtime_write_file_temp_created`
- `runtime_write_file_hash_verified`
- `runtime_write_file_committed`
- `runtime_write_succeeded`
- `runtime_write_failed`
- `runtime_write_aborted`

## Post-Write Smoke Boundary

Runtime write execution is not publish. After any future runtime write succeeds, a separate post-write smoke phase must verify generated files, route loading strategy, cache behavior, and non-publication state. Only a later separate finalization task may consider published status.

## AI And MCP Boundaries

AI Builder Agent may summarize this final implementation plan. It must not execute runtime write, click a future runtime write button, use MCP for runtime write, mark a module published, run migrations, override the kill-switch, or skip the operator acknowledgement.

MCP remains a future adapter only and must not expose runtime write, publish, migration, rollback, kill-switch override, or operator acknowledgement bypass tools in the current MVP.
