# Builder Runtime Write Post-Write Smoke MVP

## Purpose

The post-write smoke phase verifies the files committed by guarded runtime write execution. It is a verification and storage-reporting phase, not publish, module activation, migration execution, dynamic route registration, mark-published, or rollback.

The phase accepts only an execution in `runtime_write_succeeded`. It reads the runtime write report, committed runtime files, rollback manifest, backup evidence for overwrites, and the related `BuilderDefinition`. Its report is stored at `storage/app/builder-runtime-write-smoke/{definition_id}/{execution_id}/post-write-smoke.json`.

## Verification Flow

1. Require `runtime_write_succeeded`.
2. Load a valid runtime write report from its execution-scoped storage directory.
3. Confirm the report records runtime write execution while publish, migrations, route registration, mark-published, and rollback remain false.
4. Require at least one committed file and a valid rollback manifest.
5. Confirm the Builder definition is not published.
6. Revalidate every committed path against the generated-module allowlist and forbidden-path policy.
7. Reject path traversal, symbolic links, and resolved paths outside the expected generated module.
8. Confirm every committed file exists and its current SHA-256 matches the committed hash.
9. Run `php -l` for PHP files without loading or executing generated classes.
10. Decode JSON files and require valid JSON.
11. Require JS, Vue, and CSS files to be readable and non-empty.
12. Verify generated migration files as files only and record `migration_executed=false`.
13. Require a rollback-manifest committed entry for every file.
14. Require an available storage backup reference for every overwrite.
15. Store the smoke report, update execution control-plane metadata/status, and append audit events.

## Outcomes

- `runtime_write_smoke_passed`: prerequisites and all file checks passed.
- `runtime_write_smoke_failed`: the write succeeded, but integrity, syntax, JSON, rollback, or backup verification failed.
- `runtime_write_smoke_blocked`: smoke prerequisites or control-plane evidence were missing or invalid.

The smoke service modifies zero runtime files. It does not run npm, Composer, generated PHP, or generated migrations. It does not register routes, mark a module published, publish, or execute rollback.

## AI And MCP Boundaries

AI may summarize a smoke report. Running smoke requires explicit human initiation. AI and MCP may not modify runtime files, execute generated PHP, publish, run migrations, register routes, mark modules published, or execute rollback.

## Next Step

Module registration and activation remain a separate planning task. A passed smoke report does not activate or publish a module.
