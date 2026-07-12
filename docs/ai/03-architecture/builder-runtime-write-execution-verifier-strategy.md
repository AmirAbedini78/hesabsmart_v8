# ERPSMART Builder Runtime Write Execution Verifier Strategy

## Purpose

This document defines verifier expectations for a future runtime write execution MVP. It is a strategy document only and does not implement runtime write execution.

The future verifier must prove that runtime write execution is narrow, audited, reversible by manifest, and isolated to generated-module paths.

## Required Static Checks

The future verifier must assert:

- `BuilderRuntimeWriteExecutionService.php` exists only in the future implementation task.
- `POST /api/builder/publish-executions/{execution}/execute-runtime-write` exists only in the future implementation task.
- No copy-to-runtime, publish, rollback, migration execution, or dynamic route registration endpoints are added.
- The UI action label is `Execute Runtime Write`, never `Publish` or `Deploy`.
- The UI requires destructive typed confirmation.
- Runtime write code references the allowlist and forbids path traversal.
- Runtime write code writes reports under `storage/app/builder-runtime-write-executions`.
- Runtime write code does not call migration execution, route registration, rollback, publish, or public build writers.
- Runtime write code does not write Core, SaaS, Updater, Installer, vendor, node_modules, public/build, global routes, or root frontend app files.

## Required Runtime Checks

The future verifier must create a controlled generated module scenario and assert:

- post-backup readiness is passed
- kill-switch disabled case blocks execution
- kill-switch enabled test is scoped to the verifier runtime config only
- operator acknowledgement exists and is fresh
- stale operator acknowledgement blocks execution
- missing backup manifest blocks execution
- missing rollback manifest blocks execution
- missing runtime write plan blocks execution
- path traversal blocks execution
- forbidden target path blocks execution
- temporary file is written before final commit
- temporary file checksum is verified before final commit
- committed file checksum matches the staged artifact checksum
- rollback manifest is updated with committed file entries
- runtime write report is written under storage
- generated migration files may be written but are not executed
- generated module is not marked published by runtime write execution
- post-write smoke remains separate

## Required Success Case

The future success case must use a generated module path only. It must copy staged artifacts to allowlisted generated-module paths, record per-file hashes, update the rollback manifest, and leave publish state untouched.

## Required Blocking Cases

The verifier must block or report failure for:

- kill-switch disabled
- operator acknowledgement missing, revoked, expired, invalidated, or stale
- post-backup readiness missing or stale
- backup manifest missing or invalid
- rollback manifest missing or invalid
- runtime write plan blockers
- path traversal using `..`
- absolute paths outside the project
- Core, SaaS, Updater, Installer, Warehouse unless explicitly target-approved, vendor, node_modules, public/build, global routes, and root frontend paths
- partial write simulation
- checksum mismatch between staged artifact, temporary file, and committed file

## No Publish Assertions

The future verifier must assert:

- no publish endpoint is called
- no published status is set
- no route registration is performed
- no post-write smoke is treated as publish
- runtime write success is not a published module state

## No Migration Execution Assertions

Generated migration files may be copied as planned files, but the verifier must assert:

- no generated migration is executed
- no global migration execution command is invoked
- no database tables are created by runtime write execution
- migration execution remains a separate future phase

## No Core/SaaS/Updater/Installer Assertions

The verifier must assert that no files are created, modified, or deleted under:

- `modules/Core`
- `modules/SaaS`
- `modules/Updater`
- `modules/Installer`
- Core/license/update paths

Any attempt to target those paths must block before runtime write.
