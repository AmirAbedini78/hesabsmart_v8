<template>
  <ICard>
    <ICardHeader>
      <ICardHeading text="Publish Execution Records" />
    </ICardHeader>

    <ICardBody class="space-y-4">
      <IAlert variant="info">
        <IAlertBody>
          Execution record only. This acquires lock, runs preflight, and writes staging/rollback manifests under storage. It does not publish or write runtime files.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="DocumentText"
        text="Create Publish Execution Record"
        :loading="loading"
        @click="$emit('create-execution-record')"
      />

      <IAlert variant="info">
        <IAlertBody>
          Staged validation only. This checks storage artifacts and does not copy files to runtime, run migrations, register routes, or publish.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="CheckCircle"
        text="Validate Staged Files"
        :disabled="!latestExecutionId"
        :loading="validationLoading"
        @click="$emit('validate-staged-files', latestExecutionId)"
      />

      <IAlert variant="info">
        <IAlertBody>
          Plan only. This maps staged files to future runtime paths and does not copy files, run migrations, register routes, or publish.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="ClipboardList"
        text="Create Runtime Write Plan"
        :disabled="!latestExecutionId"
        :loading="runtimeWritePlanLoading"
        @click="$emit('create-runtime-write-plan', latestExecutionId)"
      />

      <IAlert variant="info">
        <IAlertBody>
          Final confirmation only. This records human confirmation state and does not write runtime files, copy staged artifacts, run migrations, register routes, or publish.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="CheckCircle"
        text="Request Runtime Write Final Confirmation"
        :disabled="!latestExecutionId"
        :loading="finalConfirmationLoading"
        @click="$emit('request-final-confirmation', latestExecutionId)"
      />

      <IAlert variant="info">
        <IAlertBody>
          Preflight only. This checks final confirmation and runtime write readiness. It does not write runtime files, copy staged artifacts, run migrations, register routes, or publish.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="ShieldCheck"
        text="Run Runtime Write Preflight"
        :disabled="!latestExecutionId"
        :loading="runtimeWritePreflightLoading"
        @click="$emit('run-runtime-write-preflight', latestExecutionId)"
      />

      <IAlert variant="info">
        <IAlertBody>
          Backup artifact only. This backs up existing future runtime target files into storage and does not copy staged files to runtime, run migrations, register routes, or publish.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="ArchiveBox"
        text="Prepare Runtime Write Backups"
        :disabled="!latestExecutionId"
        :loading="runtimeWriteBackupsLoading"
        @click="$emit('prepare-runtime-write-backups', latestExecutionId)"
      />

      <IAlert variant="info">
        <IAlertBody>
          Readiness only. This checks backup artifacts and runtime write prerequisites. It does not copy staged files to runtime, run migrations, register routes, or publish.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="ShieldCheck"
        text="Check Post-Backup Runtime Write Readiness"
        :disabled="!latestExecutionId"
        :loading="postBackupReadinessLoading"
        @click="$emit('check-post-backup-readiness', latestExecutionId)"
      />

      <IAlert variant="info">
        <IAlertBody>
          Kill-switch guard only. This checks whether runtime write is enabled, but does not execute runtime write, copy staged files, run migrations, register routes, or publish.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="ShieldCheck"
        text="Check Runtime Write Kill-Switch"
        :disabled="!latestExecutionId"
        :loading="runtimeWriteKillSwitchGuardLoading"
        @click="$emit('check-runtime-write-kill-switch-guard', latestExecutionId)"
      />

      <IAlert variant="info">
        <IAlertBody>
          Operator acknowledgement only. This records human runbook acknowledgement and does not execute runtime write, copy staged files, run migrations, register routes, or publish.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="ClipboardDocumentCheck"
        text="Request Operator Runbook Acknowledgement"
        :disabled="!latestExecutionId"
        :loading="runtimeWriteOperatorAcknowledgementLoading"
        @click="$emit('request-operator-acknowledgement', latestExecutionId)"
      />

      <IAlert variant="warning">
        <IAlertBody>
          Runtime write copies staged generated files into allowlisted runtime paths only. It does not publish, run migrations, register routes, mark the module published, or execute rollback.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="ExclamationTriangle"
        text="Execute Runtime Write"
        variant="danger"
        :disabled="!canExecuteRuntimeWrite"
        :loading="runtimeWriteExecutionLoading"
        @click="$emit('execute-runtime-write', latestExecution)"
      />

      <IAlert variant="info">
        <IAlertBody>
          Post-write smoke only. This verifies committed runtime files and does not publish, run migrations, register routes, mark the module published, modify runtime files, or execute rollback.
        </IAlertBody>
      </IAlert>

      <IButton
        class="w-full justify-center"
        icon="ShieldCheck"
        text="Run Post-Write Smoke"
        :disabled="latestExecution?.status !== 'runtime_write_succeeded'"
        :loading="runtimeWritePostWriteSmokeLoading"
        @click="$emit('run-post-write-smoke', latestExecutionId)"
      />

      <div v-if="latestReport" class="space-y-3">
        <div class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">status</span>
            <span class="font-mono">{{ latestReport.status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">runtime_writes_performed</span>
            <span class="font-mono">{{ latestReport.runtime_writes_performed }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">publish_executed</span>
            <span class="font-mono">{{ String(latestReport.publish_executed) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">lock</span>
            <span class="font-mono">acquired={{ String(latestReport.lock?.acquired) }}, released={{ String(latestReport.lock?.released) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">staging_root</span>
            <span class="break-all text-right font-mono text-xs">{{ latestReport.staging_root || '-' }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">rollback_manifest_path</span>
            <span class="break-all text-right font-mono text-xs">{{ latestReport.rollback_manifest_path || '-' }}</span>
          </div>
        </div>

        <div v-if="latestReport.forbidden_actions?.length">
          <ITextDark class="font-medium" text="Forbidden actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in latestReport.forbidden_actions" :key="action">
              {{ action }}
            </li>
          </ul>
        </div>

        <div v-if="latestReport.next_allowed_actions?.length">
          <ITextDark class="font-medium" text="Next allowed actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in latestReport.next_allowed_actions" :key="action">
              {{ action }}
            </li>
          </ul>
        </div>

        <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedLatestReport }}</pre>
      </div>

      <div v-if="validationReport" class="space-y-3">
        <ITextDark class="font-medium" text="Staged file validation" />
        <div class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">status</span>
            <span class="font-mono">{{ validationReport.status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">safe</span>
            <span class="font-mono">{{ String(validationReport.safe) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">validation_report_path</span>
            <span class="break-all text-right font-mono text-xs">{{ validationReport.validation_report_path || '-' }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">files</span>
            <span class="font-mono">{{ validationReport.summary?.total_files || 0 }}</span>
          </div>
        </div>

        <IAlert v-if="validationReport.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in validationReport.blockers" :key="blocker">
                {{ blocker }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <IAlert v-if="validationReport.warnings?.length" variant="warning">
          <IAlertBody>
            <div class="mb-1 font-medium">Warnings</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="warning in validationReport.warnings" :key="warning">
                {{ warning }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div v-if="validationReport.checks?.length">
          <ITextDark class="font-medium" text="Checks" />
          <ul class="mt-1 space-y-1 text-sm">
            <li v-for="check in validationReport.checks" :key="check.key">
              <span class="font-mono">{{ check.status }}</span> {{ check.key }} - {{ check.message }}
            </li>
          </ul>
        </div>

        <div v-if="validationReport.forbidden_actions?.length">
          <ITextDark class="font-medium" text="Forbidden actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in validationReport.forbidden_actions" :key="action">
              {{ action }}
            </li>
          </ul>
        </div>

        <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedValidationReport }}</pre>
      </div>

      <div v-if="runtimeWritePlanReport" class="space-y-3">
        <ITextDark class="font-medium" text="Runtime write plan" />
        <div class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">status</span>
            <span class="font-mono">{{ runtimeWritePlanReport.status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">safe</span>
            <span class="font-mono">{{ String(runtimeWritePlanReport.safe) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">runtime_write_plan_path</span>
            <span class="break-all text-right font-mono text-xs">{{ runtimeWritePlanReport.runtime_write_plan_path || '-' }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">planned writes</span>
            <span class="font-mono">{{ runtimeWritePlanReport.summary?.total_planned_writes || 0 }}</span>
          </div>
        </div>

        <IAlert v-if="runtimeWritePlanReport.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in runtimeWritePlanReport.blockers" :key="blocker">
                {{ blocker }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <IAlert v-if="runtimeWritePlanReport.warnings?.length" variant="warning">
          <IAlertBody>
            <div class="mb-1 font-medium">Warnings</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="warning in runtimeWritePlanReport.warnings" :key="warning">
                {{ warning }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div v-if="runtimeWritePlanReport.checks?.length">
          <ITextDark class="font-medium" text="Checks" />
          <ul class="mt-1 space-y-1 text-sm">
            <li v-for="check in runtimeWritePlanReport.checks" :key="check.key">
              <span class="font-mono">{{ check.status }}</span> {{ check.key }} - {{ check.message }}
            </li>
          </ul>
        </div>

        <div v-if="runtimeWritePlanReport.forbidden_actions?.length">
          <ITextDark class="font-medium" text="Forbidden actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in runtimeWritePlanReport.forbidden_actions" :key="action">
              {{ action }}
            </li>
          </ul>
        </div>

        <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedRuntimeWritePlanReport }}</pre>
      </div>

      <div v-if="finalConfirmationReport || finalConfirmations.length" class="space-y-3">
        <ITextDark class="font-medium" text="Runtime write final confirmations" />

        <div v-if="finalConfirmationReport" class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">status</span>
            <span class="font-mono">{{ finalConfirmationReport.status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">runtime_writes_performed</span>
            <span class="font-mono">{{ finalConfirmationReport.runtime_writes_performed }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">publish_executed</span>
            <span class="font-mono">{{ String(finalConfirmationReport.publish_executed) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">copy_to_runtime_executed</span>
            <span class="font-mono">{{ String(finalConfirmationReport.copy_to_runtime_executed) }}</span>
          </div>
        </div>

        <IAlert v-if="finalConfirmationReport?.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in finalConfirmationReport.blockers" :key="blocker">
                {{ blocker }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div
          v-for="confirmation in finalConfirmations"
          :key="confirmation.id"
          class="rounded-md border border-neutral-200 p-3 text-sm dark:border-neutral-700"
        >
          <div class="flex items-center justify-between gap-3">
            <span class="font-medium">#{{ confirmation.id }} {{ confirmation.status }}</span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ confirmation.requested_at || confirmation.created_at }}</span>
          </div>
          <div class="mt-2 grid gap-1 text-xs">
            <div>candidate_id: <span class="font-mono">{{ confirmation.candidate_id || '-' }}</span></div>
            <div>runtime_write_plan_path: <span class="break-all font-mono">{{ confirmation.runtime_write_plan_path || '-' }}</span></div>
          </div>
          <div class="mt-3 flex flex-wrap gap-2">
            <IButton
              size="sm"
              text="Grant Confirmation"
              :disabled="confirmation.status !== 'requested'"
              :loading="finalConfirmationLoading"
              @click="$emit('grant-final-confirmation', confirmation)"
            />
            <IButton
              size="sm"
              variant="secondary"
              text="Reject Confirmation"
              :disabled="confirmation.status !== 'requested'"
              :loading="finalConfirmationLoading"
              @click="$emit('reject-final-confirmation', confirmation)"
            />
            <IButton
              size="sm"
              variant="secondary"
              text="Revoke Confirmation"
              :disabled="!['requested', 'granted'].includes(confirmation.status)"
              :loading="finalConfirmationLoading"
              @click="$emit('revoke-final-confirmation', confirmation)"
            />
          </div>
        </div>

        <pre v-if="finalConfirmationReport" class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedFinalConfirmationReport }}</pre>
      </div>

      <div v-if="runtimeWritePreflightReport" class="space-y-3">
        <ITextDark class="font-medium" text="Runtime write execution preflight" />
        <div class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">status</span>
            <span class="font-mono">{{ runtimeWritePreflightReport.status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">ready_for_future_runtime_write</span>
            <span class="font-mono">{{ String(runtimeWritePreflightReport.ready_for_future_runtime_write) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">preflight_report_path</span>
            <span class="break-all text-right font-mono text-xs">{{ runtimeWritePreflightReport.preflight_report_path || '-' }}</span>
          </div>
        </div>

        <IAlert v-if="runtimeWritePreflightReport.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in runtimeWritePreflightReport.blockers" :key="blocker">
                {{ blocker }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <IAlert v-if="runtimeWritePreflightReport.warnings?.length" variant="warning">
          <IAlertBody>
            <div class="mb-1 font-medium">Warnings</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="warning in runtimeWritePreflightReport.warnings" :key="warning">
                {{ warning }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div v-if="runtimeWritePreflightReport.checks?.length">
          <ITextDark class="font-medium" text="Checks" />
          <ul class="mt-1 space-y-1 text-sm">
            <li v-for="check in runtimeWritePreflightReport.checks" :key="check.key">
              <span class="font-mono">{{ check.status }}</span> {{ check.key }} - {{ check.message }}
            </li>
          </ul>
        </div>

        <div v-if="runtimeWritePreflightReport.forbidden_actions?.length">
          <ITextDark class="font-medium" text="Forbidden actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in runtimeWritePreflightReport.forbidden_actions" :key="action">
              {{ action }}
            </li>
          </ul>
        </div>

        <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedRuntimeWritePreflightReport }}</pre>
      </div>

      <div v-if="runtimeWriteBackupsReport" class="space-y-3">
        <ITextDark class="font-medium" text="Runtime write backup artifact" />
        <div class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">status</span>
            <span class="font-mono">{{ runtimeWriteBackupsReport.status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">backup_manifest_path</span>
            <span class="break-all text-right font-mono text-xs">{{ runtimeWriteBackupsReport.backup_manifest_path || '-' }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">existing_files_backed_up</span>
            <span class="font-mono">{{ runtimeWriteBackupsReport.summary?.existing_files_backed_up || 0 }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">new_files_no_backup_needed</span>
            <span class="font-mono">{{ runtimeWriteBackupsReport.summary?.new_files_no_backup_needed || 0 }}</span>
          </div>
        </div>

        <IAlert v-if="runtimeWriteBackupsReport.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in runtimeWriteBackupsReport.blockers" :key="blocker">
                {{ blocker }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <IAlert v-if="runtimeWriteBackupsReport.warnings?.length" variant="warning">
          <IAlertBody>
            <div class="mb-1 font-medium">Warnings</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="warning in runtimeWriteBackupsReport.warnings" :key="warning">
                {{ warning }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div v-if="runtimeWriteBackupsReport.checks?.length">
          <ITextDark class="font-medium" text="Checks" />
          <ul class="mt-1 space-y-1 text-sm">
            <li v-for="check in runtimeWriteBackupsReport.checks" :key="check.key">
              <span class="font-mono">{{ check.status }}</span> {{ check.key }} - {{ check.message }}
            </li>
          </ul>
        </div>

        <div v-if="runtimeWriteBackupsReport.forbidden_actions?.length">
          <ITextDark class="font-medium" text="Forbidden actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in runtimeWriteBackupsReport.forbidden_actions" :key="action">
              {{ action }}
            </li>
          </ul>
        </div>

        <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedRuntimeWriteBackupsReport }}</pre>
      </div>

      <div v-if="postBackupReadinessReport" class="space-y-3">
        <ITextDark class="font-medium" text="Post-backup runtime write readiness" />
        <div class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">status</span>
            <span class="font-mono">{{ postBackupReadinessReport.status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">ready_for_runtime_write_execution</span>
            <span class="font-mono">{{ String(postBackupReadinessReport.ready_for_runtime_write_execution) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">readiness_report_path</span>
            <span class="break-all text-right font-mono text-xs">{{ postBackupReadinessReport.readiness_report_path || '-' }}</span>
          </div>
        </div>

        <IAlert v-if="postBackupReadinessReport.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in postBackupReadinessReport.blockers" :key="blocker">
                {{ blocker }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <IAlert v-if="postBackupReadinessReport.warnings?.length" variant="warning">
          <IAlertBody>
            <div class="mb-1 font-medium">Warnings</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="warning in postBackupReadinessReport.warnings" :key="warning">
                {{ warning }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div v-if="postBackupReadinessReport.checks?.length">
          <ITextDark class="font-medium" text="Checks" />
          <ul class="mt-1 space-y-1 text-sm">
            <li v-for="check in postBackupReadinessReport.checks" :key="check.key">
              <span class="font-mono">{{ check.status }}</span> {{ check.key }} - {{ check.message }}
            </li>
          </ul>
        </div>

        <div v-if="postBackupReadinessReport.forbidden_actions?.length">
          <ITextDark class="font-medium" text="Forbidden actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in postBackupReadinessReport.forbidden_actions" :key="action">
              {{ action }}
            </li>
          </ul>
        </div>

        <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedPostBackupReadinessReport }}</pre>
      </div>

      <div v-if="runtimeWriteKillSwitchGuardReport" class="space-y-3">
        <ITextDark class="font-medium" text="Runtime write kill-switch guard" />
        <div class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">status</span>
            <span class="font-mono">{{ runtimeWriteKillSwitchGuardReport.status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">runtime_write_enabled</span>
            <span class="font-mono">{{ String(runtimeWriteKillSwitchGuardReport.runtime_write_enabled) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">runtime_write_guard_passed</span>
            <span class="font-mono">{{ String(runtimeWriteKillSwitchGuardReport.runtime_write_guard_passed) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">guard_report_path</span>
            <span class="break-all text-right font-mono text-xs">{{ runtimeWriteKillSwitchGuardReport.guard_report_path || '-' }}</span>
          </div>
        </div>

        <IAlert v-if="runtimeWriteKillSwitchGuardReport.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in runtimeWriteKillSwitchGuardReport.blockers" :key="blocker">
                {{ blocker }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <IAlert v-if="runtimeWriteKillSwitchGuardReport.warnings?.length" variant="warning">
          <IAlertBody>
            <div class="mb-1 font-medium">Warnings</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="warning in runtimeWriteKillSwitchGuardReport.warnings" :key="warning">
                {{ warning }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div v-if="runtimeWriteKillSwitchGuardReport.checks?.length">
          <ITextDark class="font-medium" text="Checks" />
          <ul class="mt-1 space-y-1 text-sm">
            <li v-for="check in runtimeWriteKillSwitchGuardReport.checks" :key="check.key">
              <span class="font-mono">{{ check.status }}</span> {{ check.key }} - {{ check.message }}
            </li>
          </ul>
        </div>

        <div v-if="runtimeWriteKillSwitchGuardReport.forbidden_actions?.length">
          <ITextDark class="font-medium" text="Forbidden actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in runtimeWriteKillSwitchGuardReport.forbidden_actions" :key="action">
              {{ action }}
            </li>
          </ul>
        </div>

        <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedRuntimeWriteKillSwitchGuardReport }}</pre>
      </div>

      <div v-if="runtimeWriteOperatorAcknowledgementReport || operatorAcknowledgements.length" class="space-y-3">
        <ITextDark class="font-medium" text="Runtime write operator acknowledgements" />

        <div v-if="runtimeWriteOperatorAcknowledgementReport" class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">status</span>
            <span class="font-mono">{{ runtimeWriteOperatorAcknowledgementReport.status }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">runtime_writes_performed</span>
            <span class="font-mono">{{ runtimeWriteOperatorAcknowledgementReport.runtime_writes_performed }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">publish_executed</span>
            <span class="font-mono">{{ String(runtimeWriteOperatorAcknowledgementReport.publish_executed) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">copy_to_runtime_executed</span>
            <span class="font-mono">{{ String(runtimeWriteOperatorAcknowledgementReport.copy_to_runtime_executed) }}</span>
          </div>
        </div>

        <IAlert v-if="runtimeWriteOperatorAcknowledgementReport?.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in runtimeWriteOperatorAcknowledgementReport.blockers" :key="blocker">
                {{ blocker }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div
          v-for="acknowledgement in operatorAcknowledgements"
          :key="acknowledgement.id"
          class="rounded-md border border-neutral-200 p-3 text-sm dark:border-neutral-700"
        >
          <div class="flex items-center justify-between gap-3">
            <span class="font-medium">#{{ acknowledgement.id }} {{ acknowledgement.status }}</span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ acknowledgement.acknowledged_at || acknowledgement.created_at }}</span>
          </div>
          <div class="mt-2 grid gap-1 text-xs">
            <div>runbook_version: <span class="font-mono">{{ acknowledgement.runbook_version || '-' }}</span></div>
            <div>kill_switch_guard_path: <span class="break-all font-mono">{{ acknowledgement.kill_switch_guard_path || '-' }}</span></div>
            <div>post_backup_readiness_path: <span class="break-all font-mono">{{ acknowledgement.post_backup_readiness_path || '-' }}</span></div>
          </div>
          <div class="mt-3 flex flex-wrap gap-2">
            <IButton
              size="sm"
              text="Acknowledge Runbook"
              :disabled="acknowledgement.status !== 'requested'"
              :loading="runtimeWriteOperatorAcknowledgementLoading"
              @click="$emit('acknowledge-operator-runbook', acknowledgement)"
            />
            <IButton
              size="sm"
              variant="secondary"
              text="Revoke Acknowledgement"
              :disabled="!['requested', 'acknowledged'].includes(acknowledgement.status)"
              :loading="runtimeWriteOperatorAcknowledgementLoading"
              @click="$emit('revoke-operator-acknowledgement', acknowledgement)"
            />
          </div>
        </div>

        <pre v-if="runtimeWriteOperatorAcknowledgementReport" class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedRuntimeWriteOperatorAcknowledgementReport }}</pre>
      </div>

      <div v-if="runtimeWriteExecutionReport" class="space-y-3">
        <ITextDark class="font-medium" text="Runtime write execution" />
        <div class="grid gap-2 text-sm">
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">runtime_write_executed</span>
            <span class="font-mono">{{ String(runtimeWriteExecutionReport.runtime_write_executed) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">files_committed</span>
            <span class="font-mono">{{ runtimeWriteExecutionReport.files_committed?.length || 0 }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">runtime_writes_performed</span>
            <span class="font-mono">{{ runtimeWriteExecutionReport.runtime_writes_performed }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">publish_executed</span>
            <span class="font-mono">{{ String(runtimeWriteExecutionReport.publish_executed) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">migrations_run</span>
            <span class="font-mono">{{ String(runtimeWriteExecutionReport.migrations_run) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">routes_registered</span>
            <span class="font-mono">{{ String(runtimeWriteExecutionReport.routes_registered) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">module_marked_published</span>
            <span class="font-mono">{{ String(runtimeWriteExecutionReport.module_marked_published) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">rollback_executed</span>
            <span class="font-mono">{{ String(runtimeWriteExecutionReport.rollback_executed) }}</span>
          </div>
          <div class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">runtime_write_report_path</span>
            <span class="break-all text-right font-mono text-xs">{{ runtimeWriteExecutionReport.runtime_write_report_path || '-' }}</span>
          </div>
        </div>

        <IAlert v-if="runtimeWriteExecutionReport.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in runtimeWriteExecutionReport.blockers" :key="blocker">
                {{ blocker }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <IAlert v-if="runtimeWriteExecutionReport.warnings?.length" variant="warning">
          <IAlertBody>
            <div class="mb-1 font-medium">Warnings</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="warning in runtimeWriteExecutionReport.warnings" :key="warning">
                {{ warning }}
              </li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div v-if="runtimeWriteExecutionReport.checks?.length">
          <ITextDark class="font-medium" text="Checks" />
          <ul class="mt-1 space-y-1 text-sm">
            <li v-for="check in runtimeWriteExecutionReport.checks" :key="check.key">
              <span class="font-mono">{{ check.status }}</span> {{ check.key }} - {{ check.message }}
            </li>
          </ul>
        </div>

        <div v-if="runtimeWriteExecutionReport.forbidden_actions?.length">
          <ITextDark class="font-medium" text="Forbidden actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in runtimeWriteExecutionReport.forbidden_actions" :key="action">
              {{ action }}
            </li>
          </ul>
        </div>

        <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedRuntimeWriteExecutionReport }}</pre>
      </div>

      <div v-if="runtimeWritePostWriteSmokeReport" class="space-y-3">
        <ITextDark class="font-medium" text="Runtime write post-write smoke" />
        <div class="grid gap-2 text-sm">
          <div v-for="field in smokeSummaryFields" :key="field.key" class="flex justify-between gap-4">
            <span class="text-neutral-500 dark:text-neutral-400">{{ field.key }}</span>
            <span :class="field.breakable ? 'break-all text-right font-mono text-xs' : 'font-mono'">{{ field.value }}</span>
          </div>
        </div>

        <IAlert v-if="runtimeWritePostWriteSmokeReport.blockers?.length" variant="danger">
          <IAlertBody>
            <div class="mb-1 font-medium">Blockers</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="blocker in runtimeWritePostWriteSmokeReport.blockers" :key="blocker">{{ blocker }}</li>
            </ul>
          </IAlertBody>
        </IAlert>

        <IAlert v-if="runtimeWritePostWriteSmokeReport.warnings?.length" variant="warning">
          <IAlertBody>
            <div class="mb-1 font-medium">Warnings</div>
            <ul class="list-disc space-y-1 pl-5">
              <li v-for="warning in runtimeWritePostWriteSmokeReport.warnings" :key="warning">{{ warning }}</li>
            </ul>
          </IAlertBody>
        </IAlert>

        <div v-if="runtimeWritePostWriteSmokeReport.checks?.length">
          <ITextDark class="font-medium" text="Checks" />
          <ul class="mt-1 space-y-1 text-sm">
            <li v-for="check in runtimeWritePostWriteSmokeReport.checks" :key="check.key">
              <span class="font-mono">{{ check.status }}</span> {{ check.key }} - {{ check.message }}
            </li>
          </ul>
        </div>

        <div v-if="runtimeWritePostWriteSmokeReport.files?.length">
          <ITextDark class="font-medium" text="File results" />
          <div v-for="file in runtimeWritePostWriteSmokeReport.files" :key="file.runtime_path" class="mt-2 rounded-md border border-neutral-200 p-3 text-xs dark:border-neutral-700">
            <div class="break-all font-mono">{{ file.runtime_path }}</div>
            <div class="mt-1">exists={{ String(file.exists) }}, allowlisted={{ String(file.allowlisted) }}, hash_matches={{ String(file.hash_matches) }}, syntax_valid={{ String(file.syntax_valid) }}, json_valid={{ String(file.json_valid) }}</div>
          </div>
        </div>

        <div v-if="runtimeWritePostWriteSmokeReport.forbidden_actions?.length">
          <ITextDark class="font-medium" text="Forbidden actions" />
          <ul class="mt-1 list-disc space-y-1 pl-5 text-sm">
            <li v-for="action in runtimeWritePostWriteSmokeReport.forbidden_actions" :key="action">{{ action }}</li>
          </ul>
        </div>

        <pre class="max-h-96 overflow-auto whitespace-pre-wrap rounded-md bg-neutral-50 p-3 text-xs dark:bg-neutral-900">{{ formattedRuntimeWritePostWriteSmokeReport }}</pre>
      </div>

      <div v-if="records.length" class="space-y-2">
        <ITextDark class="font-medium" text="Execution records" />
        <div
          v-for="record in records"
          :key="record.id"
          class="rounded-md border border-neutral-200 p-3 text-sm dark:border-neutral-700"
        >
          <div class="flex items-center justify-between gap-3">
            <span class="font-medium">#{{ record.id }} {{ record.status }}</span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ record.created_at }}</span>
          </div>
          <div class="mt-2 grid gap-1 text-xs">
            <div>candidate_id: <span class="font-mono">{{ record.candidate_id || '-' }}</span></div>
            <div>staging_root: <span class="break-all font-mono">{{ record.staging_root || '-' }}</span></div>
            <div>rollback_manifest_path: <span class="break-all font-mono">{{ record.rollback_manifest_path || '-' }}</span></div>
          </div>
        </div>
      </div>
    </ICardBody>
  </ICard>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  records: {
    type: Array,
    default: () => [],
  },
  latestReport: Object,
  validationReport: Object,
  runtimeWritePlanReport: Object,
  finalConfirmationReport: Object,
  runtimeWritePreflightReport: Object,
  runtimeWriteBackupsReport: Object,
  postBackupReadinessReport: Object,
  runtimeWriteKillSwitchGuardReport: Object,
  runtimeWriteOperatorAcknowledgementReport: Object,
  runtimeWriteExecutionReport: Object,
  runtimeWritePostWriteSmokeReport: Object,
  finalConfirmations: {
    type: Array,
    default: () => [],
  },
  operatorAcknowledgements: {
    type: Array,
    default: () => [],
  },
  loading: Boolean,
  validationLoading: Boolean,
  runtimeWritePlanLoading: Boolean,
  finalConfirmationLoading: Boolean,
  runtimeWritePreflightLoading: Boolean,
  runtimeWriteBackupsLoading: Boolean,
  postBackupReadinessLoading: Boolean,
  runtimeWriteKillSwitchGuardLoading: Boolean,
  runtimeWriteOperatorAcknowledgementLoading: Boolean,
  runtimeWriteExecutionLoading: Boolean,
  runtimeWritePostWriteSmokeLoading: Boolean,
})

defineEmits([
  'create-execution-record',
  'validate-staged-files',
  'create-runtime-write-plan',
  'request-final-confirmation',
  'grant-final-confirmation',
  'reject-final-confirmation',
  'revoke-final-confirmation',
  'run-runtime-write-preflight',
  'prepare-runtime-write-backups',
  'check-post-backup-readiness',
  'check-runtime-write-kill-switch-guard',
  'request-operator-acknowledgement',
  'acknowledge-operator-runbook',
  'revoke-operator-acknowledgement',
  'execute-runtime-write',
  'run-post-write-smoke',
])

const latestExecutionId = computed(() =>
  props.latestReport?.execution_id || props.records?.[0]?.id || null
)

const latestExecution = computed(() => props.records?.[0] || null)

const hasAcknowledgedOperator = computed(() =>
  props.operatorAcknowledgements.some(acknowledgement => acknowledgement.status === 'acknowledged')
)

const hasKillSwitchGuardEvidence = computed(() =>
  Boolean(
    props.runtimeWriteKillSwitchGuardReport?.guard_report_path ||
      latestExecution.value?.metadata_json?.runtime_write_kill_switch_guard_path
  )
)

const hasPostBackupReadinessEvidence = computed(() =>
  Boolean(
    props.postBackupReadinessReport?.readiness_report_path ||
      latestExecution.value?.metadata_json?.post_backup_runtime_write_readiness_path
  )
)

const canExecuteRuntimeWrite = computed(() =>
  Boolean(
    latestExecution.value &&
      ['runtime_write_operator_acknowledged', 'runtime_write_guard_passed'].includes(latestExecution.value.status) &&
      hasAcknowledgedOperator.value &&
      hasKillSwitchGuardEvidence.value &&
      hasPostBackupReadinessEvidence.value
  )
)

const formattedLatestReport = computed(() =>
  props.latestReport ? JSON.stringify(props.latestReport, null, 2) : 'Not run yet.'
)

const formattedValidationReport = computed(() =>
  props.validationReport ? JSON.stringify(props.validationReport, null, 2) : 'Not run yet.'
)

const formattedRuntimeWritePlanReport = computed(() =>
  props.runtimeWritePlanReport ? JSON.stringify(props.runtimeWritePlanReport, null, 2) : 'Not run yet.'
)

const formattedFinalConfirmationReport = computed(() =>
  props.finalConfirmationReport ? JSON.stringify(props.finalConfirmationReport, null, 2) : 'Not run yet.'
)

const formattedRuntimeWritePreflightReport = computed(() =>
  props.runtimeWritePreflightReport ? JSON.stringify(props.runtimeWritePreflightReport, null, 2) : 'Not run yet.'
)

const formattedRuntimeWriteBackupsReport = computed(() =>
  props.runtimeWriteBackupsReport ? JSON.stringify(props.runtimeWriteBackupsReport, null, 2) : 'Not run yet.'
)

const formattedPostBackupReadinessReport = computed(() =>
  props.postBackupReadinessReport ? JSON.stringify(props.postBackupReadinessReport, null, 2) : 'Not run yet.'
)

const formattedRuntimeWriteKillSwitchGuardReport = computed(() =>
  props.runtimeWriteKillSwitchGuardReport ? JSON.stringify(props.runtimeWriteKillSwitchGuardReport, null, 2) : 'Not run yet.'
)

const formattedRuntimeWriteOperatorAcknowledgementReport = computed(() =>
  props.runtimeWriteOperatorAcknowledgementReport ? JSON.stringify(props.runtimeWriteOperatorAcknowledgementReport, null, 2) : 'Not run yet.'
)

const formattedRuntimeWriteExecutionReport = computed(() =>
  props.runtimeWriteExecutionReport ? JSON.stringify(props.runtimeWriteExecutionReport, null, 2) : 'Not run yet.'
)

const smokeSummaryFields = computed(() => {
  const report = props.runtimeWritePostWriteSmokeReport
  const summary = report?.summary || {}

  return [
    { key: 'post_write_smoke_passed', value: String(report?.post_write_smoke_passed) },
    { key: 'status', value: report?.status || '-' },
    { key: 'smoke_report_path', value: report?.smoke_report_path || '-', breakable: true },
    { key: 'total_files', value: summary.total_files || 0 },
    { key: 'hash_matches', value: summary.hash_matches || 0 },
    { key: 'php_syntax_passed', value: summary.php_syntax_passed || 0 },
    { key: 'json_valid', value: summary.json_valid || 0 },
    { key: 'migration_files_not_executed', value: summary.migration_files_not_executed || 0 },
    { key: 'rollback_entries_found', value: summary.rollback_entries_found || 0 },
  ]
})

const formattedRuntimeWritePostWriteSmokeReport = computed(() =>
  props.runtimeWritePostWriteSmokeReport ? JSON.stringify(props.runtimeWritePostWriteSmokeReport, null, 2) : 'Not run yet.'
)
</script>
