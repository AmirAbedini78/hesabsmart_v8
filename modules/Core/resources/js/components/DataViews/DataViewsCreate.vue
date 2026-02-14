<template>
  <IModal
    id="createNewTableView"
    v-model:visible="model"
    size="sm"
    :ok-text="$t('core::app.create')"
    :ok-disabled="form.busy"
    :title="$t('core::data_views.create_new')"
    form
    @submit="create"
    @shown="() => $refs.inputNameRef.focus()"
  >
    <IFormGroup label-for="name" :label="$t('core::data_views.name')" required>
      <IFormInput id="name" ref="inputNameRef" v-model="form.name" />

      <IFormError :error="form.getError('name')" />
    </IFormGroup>

    <IFormGroup :label="$t('core::data_views.share.with')" required>
      <IDropdown adaptive-width>
        <IDropdownButton variant="secondary" class="w-full">
          {{
            form.is_shared
              ? $t('core::data_views.share.everyone')
              : $t('core::data_views.share.private')
          }}
        </IDropdownButton>

        <IDropdownMenu>
          <IDropdownItem
            :active="form.is_shared === false"
            @click="form.is_shared = false"
          >
            <IDropdownItemLabel :text="$t('core::data_views.share.private')" />

            <IDropdownItemDescription
              :text="$t('core::data_views.share.private_info')"
            />
          </IDropdownItem>

          <IDropdownItem
            :active="form.is_shared === true"
            @click="form.is_shared = true"
          >
            <IDropdownItemLabel :text="$t('core::data_views.share.everyone')" />

            <IDropdownItemDescription
              :text="$t('core::data_views.share.everyone_info')"
            />
          </IDropdownItem>
        </IDropdownMenu>
      </IDropdown>

      <IFormError :error="form.getError('is_shared')" />
    </IFormGroup>
  </IModal>
</template>

<script setup>
import { watch } from 'vue'
import pick from 'lodash/pick'

import { useDataViews } from '@/Core/composables/useDataViews'
import { useForm } from '@/Core/composables/useForm'

const props = defineProps({
  identifier: { type: String, required: true },
})

const emit = defineEmits(['created'])

const model = defineModel('visible', { type: Boolean })

watch(model, newVal => {
  if (newVal) {
    initiateNewViewCreation()
  }
})

const { activeView, addView } = useDataViews(props.identifier)

const { form } = useForm({
  name: null,
  is_shared: false,
})

function initiateNewViewCreation() {
  form.reset()

  // We won't clone the rules into the copy of the view becauase the rules
  // may contain rules with authorization and if this filter is shared, won't work.
  form.set(pick(activeView.value, ['identifier', 'config']))
}

async function create() {
  const view = await form.post('/views')

  addView(view)

  form.reset()

  emit('created', view)
}
</script>
