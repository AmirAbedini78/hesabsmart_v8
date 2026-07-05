# Builder Runtime Write Failure And Rollback Policy

Status: policy only. Runtime write and rollback execution are not implemented.

## Failure Handling

- Detect partial writes.
- Detect failed temporary file creation.
- Detect failed staged-to-temporary copy.
- Detect failed checksum verification.
- Detect failed atomic rename.
- Detect backup hash mismatch.
- Detect rollback manifest incompleteness.

Each failure must write an audit event and a storage report. Runtime write must stop before continuing to the next file unless a future policy explicitly allows retry.

## Rollback Policy

Rollback execution is a separate future phase. It must require human confirmation and must not be automatic in the MVP. Rollback must use the rollback manifest, backup manifest, and written-file hashes to decide what can be restored or removed safely.

## Post-Write Smoke Failure

Post-write smoke is a separate phase after future runtime write. If smoke fails, the operator must review the runtime write report, rollback manifest, and backups before any rollback action. AI may summarize evidence but must not execute rollback.

## Audit Events

Future implementation must audit temp-write failures, commit failures, aborts, rollback start/failure/success, and post-write smoke results.
