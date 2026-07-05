# Builder Runtime Write Backup Artifact MVP

This MVP adds a control-plane/storage-only preparation step after runtime write execution preflight has passed.

It is not runtime write, not publish, not rollback, and not migration execution. It never copies staged artifacts into runtime paths and never marks a Builder definition or module as published.

## Purpose

The backup artifact step reads the runtime write plan and inspects each planned future runtime path. Existing target files are copied into backup storage only:

`storage/app/builder-publish-backups/{definition_id}/{execution_id}`

New target files are recorded with `existed_before=false`; their runtime directories and files are not created. Planned migrations are recorded as planned migration entries only and are not run.

## Safety Boundaries

- Allowed writes are limited to `storage/app/builder-publish-backups`.
- The backup manifest is written to `backup-manifest.json` under the backup root.
- Existing future runtime target files may be read and copied into backup storage.
- Staged artifacts are not copied into runtime paths.
- Runtime module files are not written.
- Generated migrations are not run.
- Runtime routes are not registered.
- Publish and rollback remain forbidden.

## Rollback Manifest Draft

The service updates the existing rollback manifest draft with backup references only. This allows future runtime-write and rollback planning to reason about existing-file backups without performing runtime writes.

## AI And MCP Restrictions

AI Builder Agent may summarize the backup artifact report when explicitly human initiated. It must not autonomously prepare backups, treat backups as runtime write, copy staged files into runtime paths, run migrations, or publish. MCP remains future-only and must not expose runtime write execution.

## Next Steps

- Post-backup readiness preflight.
- Runtime write execution architecture review.
- Actual runtime write implementation only in a separate safety-critical task.
