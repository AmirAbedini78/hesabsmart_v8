<template>
  <IDropdown adaptive-width v-bind="$attrs">
    <IDropdownButton variant="secondary" class="w-full truncate">
      <span class="truncate" v-text="activePipeline.name" />
    </IDropdownButton>

    <IDropdownMenu>
      <IDropdownItem
        v-for="pipeline in pipelines"
        :key="pipeline.id"
        :text="pipeline.name"
        :active="activePipelineId === pipeline.id"
        @click="changePipeline(pipeline.id)"
      />

      <template v-if="pipelines.length > 1 || canUpdateActivePipeline">
        <IDropdownSeparator />

        <IDropdownItem
          v-if="pipelines.length > 1"
          class="font-medium"
          icon="Bars3BottomLeft"
          :text="$t('deals::deal.pipeline.reorder')"
          @click="pipelinesBeingReordered = true"
        />

        <IDropdownItem
          v-if="canUpdateActivePipeline"
          class="font-medium"
          icon="PencilAlt"
          :to="{
            name: 'edit-pipeline',
            params: { id: activePipeline.id },
          }"
          :text="$t('deals::deal.pipeline.edit')"
        />
      </template>
    </IDropdownMenu>
  </IDropdown>

  <IModal v-model:visible="pipelinesBeingReordered" size="sm" hide-header>
    <ITextDisplay class="mb-2">
      {{ $t('deals::deal.pipeline.reorder') }}
    </ITextDisplay>

    <SortableDraggable
      v-model="pipelines"
      item-key="id"
      class="space-y-2 pb-2 last:pb-0"
      handle="[data-sortable-handle='pipelines-order']"
      v-bind="$draggable.common"
      @change="storage.pipeline_id = null"
    >
      <template #item="{ element }">
        <div
          class="flex justify-between rounded-lg border border-neutral-300 p-3 text-sm dark:border-neutral-500/30"
        >
          <p
            class="font-medium text-neutral-700 dark:text-neutral-200"
            v-text="element.name"
          />

          <div data-sortable-handle="pipelines-order" class="cursor-move">
            <Icon icon="Selector" class="size-5 text-neutral-500" />
          </div>
        </div>
      </template>
    </SortableDraggable>

    <template #modal-footer="{ cancel }">
      <div class="text-right">
        <IButton
          variant="secondary"
          :text="$t('core::app.hide')"
          @click="cancel"
        />
      </div>
    </template>
  </IModal>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useStorage } from '@vueuse/core'

import { usePipelines } from '../composables/usePipelines'

defineOptions({ inheritAttrs: false })

const emit = defineEmits(['activated', 'changed'])

const pipelinesBeingReordered = ref(false)
const activePipelineId = ref(null)
const { orderedPipelinesForDraggable: pipelines } = usePipelines()

const storage = useStorage(
  'deals',
  {
    pipeline_id: null,
  },
  null,
  { mergeDefaults: true }
)

activePipelineId.value = storage.value.pipeline_id || pipelines.value[0].id

emit('activated', activePipelineId.value)

const activePipeline = computed(
  () =>
    pipelines.value.find(({ id }) => id === parseInt(activePipelineId.value)) ||
    {}
)

const canUpdateActivePipeline = computed(() =>
  Boolean(activePipeline.value.authorizations?.update)
)

function changePipeline(id) {
  activePipelineId.value = id
  storage.value.pipeline_id = id

  emit('changed', id)
}
</script>
