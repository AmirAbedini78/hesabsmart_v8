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
  finalConfirmations: {
    type: Array,
    default: () => [],
  },
  loading: Boolean,
  validationLoading: Boolean,
  runtimeWritePlanLoading: Boolean,
  finalConfirmationLoading: Boolean,
  runtimeWritePreflightLoading: Boolean,
  runtimeWriteBackupsLoading: Boolean,
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
])

const latestExecutionId = computed(() =>
  props.latestReport?.execution_id || props.records?.[0]?.id || null
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
</script>
