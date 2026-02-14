<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="relative">
    <template v-if="displayPanelsManagement">
      <div class="mb-4">
        <ITextDisplay
          :text="$t('core::app.record_view.sections.edit_heading')"
        />

        <IText :text="$t('core::app.record_view.sections.edit_subheading')" />
      </div>

      <SortableDraggable
        item-key="id"
        class="space-y-2"
        handle="[data-sortable-handle='panels']"
        :model-value="panels"
        v-bind="$draggable.common"
        @update:model-value="$emit('update:panels', $event)"
      >
        <template #item="{ element }">
          <div
            class="flex items-center rounded-lg border border-neutral-200 bg-white px-4 py-3 dark:border-neutral-500/30 dark:bg-neutral-900"
          >
            <div class="grow">
              <IFormCheckboxField>
                <IFormCheckbox v-model:checked="checked[element.id]" />

                <IFormCheckboxLabel :text="element.heading || element.id" />
              </IFormCheckboxField>
            </div>

            <div data-sortable-handle="panels" class="cursor-move">
              <Icon icon="Selector" class="size-5 text-neutral-500" />
            </div>
          </div>
        </template>
      </SortableDraggable>

      <div class="mt-3 flex items-center justify-end space-x-1.5">
        <IButton
          :text="$t('core::app.cancel')"
          basic
          @click="displayPanelsManagement = false"
        />

        <IButton
          variant="primary"
          :disabled="panelsAreBeingSaved"
          :loading="panelsAreBeingSaved"
          :text="$t('core::app.save')"
          soft
          @click="savePanels"
        />
      </div>
    </template>

    <div v-show="!displayPanelsManagement">
      <template v-for="panel in enabledPanels" :key="panel.id">
        <slot :panel="panel">
          <component v-bind="$attrs" :is="panel.component" :panel="panel" />
        </slot>
      </template>
    </div>

    <IDropdown v-if="$gate.isSuperAdmin()" v-once placement="bottom-start">
      <IDropdownButton
        v-show="!displayPanelsManagement"
        as="button"
        class="absolute -top-7 left-2 rotate-90 p-1 text-neutral-500 dark:text-neutral-300 lg:-left-7 lg:top-1.5 lg:rotate-0"
        no-caret
      >
        <Icon icon="EllipsisVerticalSolid" class="size-4" />
      </IDropdownButton>

      <IDropdownMenu>
        <IDropdownItem
          :text="$t('core::app.record_view.manage_sidebar')"
          @click="displayPanelsManagement = true"
        />
      </IDropdownMenu>
    </IDropdown>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  panels: { type: Array, required: true },
  identifier: { type: String, required: true },
})

const emit = defineEmits(['update:panels'])

const enabledPanels = computed(() =>
  props.panels.filter(panel => panel.enabled === true)
)

const checked = ref({})

props.panels.forEach(section => {
  checked.value[section.id] = section.enabled
})

const panelsAreBeingSaved = ref(false)
const displayPanelsManagement = ref(false)

async function savePanels() {
  panelsAreBeingSaved.value = true

  await Innoclapps.request().post('/settings', {
    [props.identifier + '_panels']: props.panels.map((panel, index) => ({
      id: panel.id,
      order: index + 1,
      enabled: checked.value[panel.id],
    })),
  })

  displayPanelsManagement.value = false
  panelsAreBeingSaved.value = false

  const newValue = props.panels.map((section, index) =>
    Object.assign({}, section, {
      order: index + 1,
      enabled: checked.value[section.id],
    })
  )

  emit('update:panels', newValue)
}
</script>
