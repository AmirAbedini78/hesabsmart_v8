<template>
  <IModal
    id="viewEditModal"
    v-model:visible="model"
    size="sm"
    :ok-text="$t('core::app.save')"
    :ok-disabled="form.busy"
    :title="$t('core::data_views.edit_view')"
    form
    @submit="update"
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
            v-if="!hasRulesAppliedWithAuthorization"
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

    <IAlert v-if="hasRulesAppliedWithAuthorization" class="mb-3">
      <IAlertBody>
        {{
          $t('core::data_views.cannot_be_shared', {
            rules: rulesLabelsWithAuthorization.join(', '),
          })
        }}
      </IAlertBody>
    </IAlert>
  </IModal>
</template>

<script setup>
import { watch } from 'vue'

import { useDataViews } from '@/Core/composables/useDataViews'
import { useForm } from '@/Core/composables/useForm'
import { useQueryBuilder } from '@/Core/composables/useQueryBuilder'

const props = defineProps({
  identifier: { type: String, required: true },
  view: { type: Object, required: true },
})

const emit = defineEmits(['updated'])

const model = defineModel('visible', { type: Boolean })

const { hasRulesAppliedWithAuthorization, rulesLabelsWithAuthorization } =
  useQueryBuilder(props.identifier)

const { patchView } = useDataViews(props.identifier)

const { form } = useForm({
  name: props.view.name,
  is_shared: props.view.is_shared,
})

watch(hasRulesAppliedWithAuthorization, (newVal, oldVal) => {
  if (!oldVal && newVal) {
    form.is_shared = false
  }
})

async function update() {
  const updatedView = await form.put(`/views/${props.view.id}`)

  patchView(updatedView)

  emit('updated', updatedView)
}
</script>
