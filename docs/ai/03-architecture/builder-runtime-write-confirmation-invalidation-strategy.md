# Builder Runtime Write Confirmation Invalidation Strategy

Future runtime write final confirmation must be invalidated whenever the reviewed runtime write plan can no longer be trusted as the exact plan a human confirmed.

## Invalidation Rules

Final confirmation must become invalid when:

- definition checksum changed
- runtime write plan was regenerated
- staged validation was regenerated
- execution status changed away from `runtime_write_planned`
- approval request was revoked, rejected, invalidated, or expired
- candidate snapshot changed
- a blocker appears in the runtime write plan
- runtime write plan path is missing
- runtime write plan JSON is invalid
- confirmation expired
- confirming user permissions changed
- runtime path allowlist changed

## Enforcement Notes

A future runtime write phase must re-check final confirmation immediately before any runtime write. It must not rely on stale UI state, cached RAG summaries, MCP context, or previous approval state.

If any invalidation rule is triggered, the future system should require a regenerated staged validation report, regenerated runtime write plan, and a new explicit human final confirmation. The current MVP implements none of this persistence or execution behavior.
