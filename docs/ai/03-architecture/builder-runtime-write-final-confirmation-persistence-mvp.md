# Builder Runtime Write Final Confirmation Persistence MVP

This MVP implements persistent human final confirmation records for runtime write plans. It is control-plane only.

Final confirmation does not publish, does not copy staged artifacts, does not write runtime module files, does not run generated migrations, does not register runtime routes, and does not mark modules as published.

## Implemented Flow

Final confirmations bind to a `BuilderPublishExecution` that has already reached `runtime_write_planned`.

Supported statuses:

- `requested`
- `granted`
- `rejected`
- `revoked`
- `invalidated`
- `expired`

Supported transitions:

- request confirmation for a runtime write plan
- grant a fresh requested confirmation
- reject a requested confirmation
- revoke a requested or granted confirmation
- invalidate instead of granting when freshness checks fail

## Binding Fields

Each confirmation stores:

- builder definition id
- publish execution id
- linked approval request id when present
- candidate id
- definition checksum
- runtime write plan path
- staged validation report path
- candidate snapshot path
- approved candidate preflight JSON
- runtime write plan JSON
- requester/decider metadata

## Freshness And Invalidation

Granting confirmation reruns freshness checks. If the execution status, plan path, plan JSON, definition checksum, linked approval request, staged validation report, candidate id, plan blockers, or expiration state is stale, the confirmation is marked `invalidated` and an audit event is written.

## Audit

The service writes append-only audit events:

- `runtime_write_confirmation_requested`
- `runtime_write_confirmation_granted`
- `runtime_write_confirmation_rejected`
- `runtime_write_confirmation_revoked`
- `runtime_write_confirmation_invalidated`

## Safety Boundaries

AI Builder Agent may summarize confirmation state, but must not autonomously request, grant, reject, or revoke final confirmation. MCP must not expose confirmation tools in the current MVP and must not bypass human confirmation.

Next steps remain separate tasks: runtime write execution architecture review, backup artifact MVP, post-write smoke planning, and any future runtime write execution.
