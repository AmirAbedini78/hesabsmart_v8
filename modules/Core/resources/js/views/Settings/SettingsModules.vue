<template>
  <IAlert v-if="!passesZipRequirement" variant="danger" class="mb-6">
    <IAlertBody>
      {{ $t('core::modules.zip_is_required') }}
    </IAlertBody>
  </IAlert>

  <div v-if="hasModulesUploadedSuccessfully" class="mb-2 text-right">
    <IButton variant="success" ghost @click="refreshPage">
      {{ $t('core::app.reload') }}
    </IButton>
  </div>

  <div v-if="uploadResponse" class="mb-6 space-y-3">
    <IAlert
      v-for="(data, moduleName) in uploadResponse"
      :key="moduleName"
      :variant="data.success ? 'success' : 'warning'"
    >
      <IAlertBody>
        <template v-if="data.success">
          [{{ moduleName }}] {{ $t('core::modules.uploaded') }}
        </template>

        <template v-else>[{{ moduleName }}] {{ data.errorReason }}</template>
      </IAlertBody>
    </IAlert>
  </div>

  <ICardHeader>
    <ICardHeading :text="$t('core::modules.modules')" />

    <MediaUpload
      extensions="zip"
      :select-file-text="`${$t('core::modules.upload_module')} (.zip)`"
      :show-output="false"
      :allow-cancel="false"
      :disabled="!passesZipRequirement"
      :multiple="false"
      :action-url="`${$scriptConfig('apiURL')}/modules`"
      @file-uploaded="fileUploaded"
    />
  </ICardHeader>

  <ICard :overlay="modules.length === 0">
    <div class="px-6">
      <ITable class="[--gutter:theme(spacing.6)]" bleed>
        <ITableHead class="bg-neutral-50 dark:bg-neutral-500/10">
          <ITableRow>
            <ITableHeader>#</ITableHeader>

            <ITableHeader>
              {{ $t('core::modules.name') }}
            </ITableHeader>

            <ITableHeader>
              {{ $t('core::modules.description') }}
            </ITableHeader>

            <ITableHeader>
              {{ $t('core::modules.version') }}
            </ITableHeader>

            <ITableHeader width="8%" />
          </ITableRow>
        </ITableHead>

        <ITableBody>
          <ITableRow
            v-for="(mod, idx) in modules"
            :key="mod.id"
            :class="mod.disabled ? 'opacity-60' : ''"
          >
            <ITableCell>
              {{ idx + 1 }}
            </ITableCell>

            <ITableCell>
              {{ mod.name }}

              <ITextSmall v-if="!mod.is_core && mod.version">
                ({{ mod.version }})
              </ITextSmall>

              <IBadge v-if="mod.is_core" variant="info">
                {{ $t('core::modules.core_module') }}
              </IBadge>
            </ITableCell>

            <ITableCell>
              <!-- eslint-disable-next-line vue/no-v-html -->
              <div v-html="mod.description || 'N/A'" />
            </ITableCell>

            <ITableCell>
              {{
                mod.is_core ? $scriptConfig('version') : mod.version || 'N/A'
              }}
            </ITableCell>

            <ITableCell>
              <ITableRowActions v-if="!mod.is_core">
                <ITableRowAction
                  v-if="mod.disabled"
                  :text="$t('core::modules.enable')"
                  @click="enableModule(mod.lower_name)"
                />

                <ITableRowAction
                  v-else
                  :text="$t('core::modules.disable')"
                  @click="disableModule(mod.lower_name)"
                />

                <ITableRowAction
                  v-if="mod.disabled"
                  :text="$t('core::modules.delete')"
                  @click="deleteModule(mod.lower_name)"
                />
              </ITableRowActions>
            </ITableCell>
          </ITableRow>
        </ITableBody>
      </ITable>
    </div>
  </ICard>
</template>

<script setup>
import { computed, ref } from 'vue'

import MediaUpload from '@/Core/components/Media/MediaUpload.vue'
import { useApp } from '@/Core/composables/useApp'

const { environment, scriptConfig } = useApp()

const modules = ref([])
const uploadResponse = ref(null)
const passesZipRequirement = scriptConfig('requirements.zip')

const hasModulesUploadedSuccessfully = computed(() => {
  return Object.values(uploadResponse.value || {}).some(data => data.success)
})

async function retrieveModules() {
  const { data } = await Innoclapps.request('/modules')

  modules.value = data
}

async function enableModule(name) {
  await Innoclapps.request().post(`/modules/${name}/enable`)
  await handleAfterModuleChange()

  window.location.reload(true)
}

async function disableModule(name) {
  await Innoclapps.confirm()

  await Innoclapps.request().post(`/modules/${name}/disable`)
  await handleAfterModuleChange()

  window.location.reload(true)
}

async function deleteModule(name) {
  await Innoclapps.confirm()

  await Innoclapps.request().delete(`/modules/${name}`)
  await handleAfterModuleChange()

  window.location.reload(true)
}

async function handleAfterModuleChange() {
  await Innoclapps.request().post('/tools/clear-cache')
  await Innoclapps.request().post('/tools/json-language')

  if (environment === 'production') {
    await Innoclapps.request().post('/tools/optimize')
  }
}

function fileUploaded(response) {
  uploadResponse.value = response
}

function refreshPage() {
  window.location.reload()
}

retrieveModules()
</script>
