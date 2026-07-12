# ERPSMART Builder Runtime Write UI Safety Requirements

## Purpose

This document defines UI safety requirements for the future runtime write execution MVP. It is requirements-only. No runtime write UI action is implemented by this batch.

## Visibility Requirements

The future `Execute Runtime Write` button must be hidden unless all prerequisites pass:

- post-backup runtime write readiness passed
- kill-switch guard passed
- operator runbook acknowledgement acknowledged and fresh
- runtime write plan valid
- backup manifest valid
- rollback manifest valid
- allowlist valid
- runtime write lock available

Readiness, guard pass, and acknowledgement do not execute runtime write by themselves.

## Confirmation Requirements

The future runtime write UI action must show a destructive confirmation dialog. The operator must type either:

- the exact generated module name, or
- the execution UUID

The confirmation must clearly state that runtime write is not publish, does not run migrations, does not register routes, does not execute rollback, and requires post-write smoke before any published state can be considered.

## Forbidden Labels

The future runtime write UI must not use these labels for the runtime write action:

- `Publish`
- `Deploy`
- `Run migrations`
- `Rollback`

The only allowed future action label is `Execute Runtime Write`.

## AI And MCP Restrictions

The runtime write button must not be available to AI automation. AI Builder Agent may summarize prerequisites and reports, but must not click the button, simulate typed confirmation, or invoke MCP to execute runtime write.

MCP must not expose a runtime write UI click, runtime write endpoint, publish endpoint, migration endpoint, rollback endpoint, or kill-switch override tool in the current MVP.

## Post-Write Smoke Boundary

Runtime write is not publish and is not rollback. A separate post-write smoke phase is required after any future runtime write execution. Published status must remain separate from runtime write execution.
