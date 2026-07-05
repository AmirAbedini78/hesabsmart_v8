# Builder Runtime Write Kill-Switch Policy

Status: policy only. Runtime write execution is not implemented.

Runtime write must be disabled by default and guarded at multiple levels before any future file write can occur.

## Required Kill Switches

- Global config flag: `builder.runtime_write_enabled=false` by default.
- Environment guard: `BUILDER_RUNTIME_WRITE_ENABLED=false` by default.
- Per-definition guard.
- Per-execution guard.
- Emergency abort before each file write.
- Maximum files per execution.
- Maximum total bytes per execution.
- Forbid overwriting existing files unless backup exists.
- Forbid migrations in runtime write phase.
- Forbid route registration in runtime write phase.
- Forbid `public/build` writes.
- Forbid Core, SaaS, Updater, and Installer writes.

## Non-Override Policy

AI Builder Agent and MCP must not override kill-switches. Future implementation must check kill-switches in-process immediately before every file write, not only at request start.

## Failure Behavior

If a kill-switch changes while execution is in progress, the runtime write must abort before the next file write, record audit evidence, and leave rollback manifest state inspectable.
