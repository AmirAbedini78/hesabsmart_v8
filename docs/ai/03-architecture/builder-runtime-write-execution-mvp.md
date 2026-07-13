# ERPSMART Builder Runtime Write Execution MVP

## Purpose

The runtime write execution MVP is the first guarded operation that may copy staged generated artifacts into allowlisted runtime paths. It is not publish. It does not run generated migrations, dynamically register routes, mark modules published, execute rollback, write Core/SaaS/Updater/Installer code, write public/build, write vendor, write node_modules, or modify license/update code.

## Required Preconditions

Runtime write execution requires all safety-chain evidence to be current:

- `builder.runtime_write.enabled` is true.
- execution status is `runtime_write_operator_acknowledged`, or the latest operator acknowledgement is acknowledged and fresh.
- post-backup readiness report exists and has `ready_for_runtime_write_execution: true`.
- kill-switch guard report exists and has `runtime_write_guard_passed: true`.
- latest operator acknowledgement exists and is acknowledged.
- runtime write plan exists and is valid JSON.
- backup manifest exists and is valid JSON.
- rollback manifest exists and is valid JSON.
- planned runtime target paths are allowlisted.
- no planned path traverses with `..` or targets forbidden scopes.
- configured max file and byte limits are respected.
- overwrite actions have backup records.

## Kill-Switch Behavior

Runtime writes remain disabled by default. The service checks `config('builder.runtime_write.enabled')`; missing or false config aborts before runtime paths are touched. Verifiers may temporarily set config in test runtime, but `.env` must not be edited.

## Operator Acknowledgement

Successful operator runbook acknowledgement transitions the execution to `runtime_write_operator_acknowledged`. Acknowledgement itself does not execute runtime write; it only unlocks the next guarded state.

## Lock Behavior

The execution service acquires a Laravel cache lock:

`builder:runtime-write:{execution_id}`

If the lock cannot be acquired, execution aborts before runtime writes.

## Allowlist Behavior

Allowed runtime write targets are generated module scopes only:

- `modules/{GeneratedModule}/App/Models`
- `modules/{GeneratedModule}/App/Http/Controllers`
- `modules/{GeneratedModule}/App/Http/Resources`
- `modules/{GeneratedModule}/database/migrations` as files only
- `modules/{GeneratedModule}/resources/js`
- `modules/{GeneratedModule}/routes`

Forbidden scopes include Core, SaaS, Updater, Installer, public/build, vendor, node_modules, global routes, and root frontend app files.

## Atomic Write Behavior

For each planned write, the service reads the staged source file, writes a temporary file next to the runtime target, verifies SHA-256 checksum equality, then renames the temporary file into place. The report records every committed file.

## Backup And Rollback Manifest Behavior

Overwrite actions require backup records created by the backup artifact phase. Runtime write execution updates the rollback manifest with committed file entries only. Rollback execution remains unimplemented and separate.

## Non-Publish Boundaries

Runtime write execution explicitly reports:

- `publish_executed: false`
- `migrations_run: false`
- `routes_registered: false`
- `module_marked_published: false`
- `rollback_executed: false`

Post-write smoke must be implemented and run as a separate future task before any published state can be considered.

## AI And MCP Restrictions

AI may summarize runtime write reports. AI may not autonomously execute runtime write, click `Execute Runtime Write`, bypass typed confirmation, publish, run migrations, or execute rollback. MCP must not expose runtime write execution as an autonomous tool in the current MVP.
