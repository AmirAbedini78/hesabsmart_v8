<template>
  <ICard as="form" :overlay="!hasFields" @submit.prevent="create">
    <ICardBody>
      <FormFields
        :form="form"
        :fields="fields"
        :resource-name="resourceName"
        @update-field-value="form.fill($event.attribute, $event.value)"
        @set-initial-value="form.set($event.attribute, $event.value)"
      />

      <AssociationsPopover
        v-model="form.associations"
        placement="bottom-start"
        :primary-record="relatedResource"
        :primary-resource-name="viaResource"
        :primary-record-disabled="true"
        :initial-associateables="relatedResource"
      />
    </ICardBody>

    <ICardFooter class="flex flex-col sm:flex-row sm:items-center">
      <CreateFollowUpTask
        ref="createFollowUpTaskRef"
        v-model="form.task_date"
        class="grow"
      />

      <div class="mt-2 space-y-2 sm:mt-0 sm:space-x-2 sm:space-y-0">
        <IButton
          class="w-full sm:w-auto"
          variant="secondary"
          :text="$t('core::app.cancel')"
          @click="$emit('cancel')"
        />

        <IButton
          class="w-full sm:w-auto"
          variant="primary"
          :disabled="form.busy"
          :text="$t('calls::call.add')"
          @click="create"
        />
      </div>
    </ICardFooter>
  </ICard>
</template>

<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import AssociationsPopover from '@/Core/components/AssociationsPopover.vue'
import { useForm } from '@/Core/composables/useForm'
import { useResourceable } from '@/Core/composables/useResourceable'
import { useResourceFields } from '@/Core/composables/useResourceFields'

import CreateFollowUpTask from '@/Activities/components/CreateFollowUpTask.vue'
import { useActivities } from '@/Activities/composables/useActivities'

const props = defineProps({
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
  relatedResource: { required: true, type: Object },
})

defineEmits(['cancel'])

const synchronizeResource = inject('synchronizeResource')
const incrementResourceCount = inject('incrementResourceCount')

const resourceName = Innoclapps.resourceName('calls')

const { t } = useI18n()

const { fields, hasFields, getCreateFields } = useResourceFields()
const { createFollowUpActivity } = useActivities()
const { createResource } = useResourceable(resourceName)

const { form } = useForm({
  task_date: null,
  associations: {
    [props.viaResource]: [props.viaResourceId],
  },
})

const createFollowUpTaskRef = ref(null)

async function handleCallCreated(call) {
  if (form.task_date) {
    let activity = await createFollowUpActivity(
      form.task_date,
      props.viaResource,
      props.viaResourceId,
      props.relatedResourceDisplayName,
      {
        note: t('calls::call.follow_up_task_body', {
          content: call.body,
        }),
      }
    )

    createFollowUpTaskRef.value.reset()

    if (activity) {
      synchronizeResource({ activities: [activity] })
      incrementResourceCount('incomplete_activities_for_user_count')
    }
  }

  synchronizeResource({ calls: [call] })
  incrementResourceCount('calls_count')

  Innoclapps.success(t('calls::call.created'))
  form.reset()
}

function create() {
  createResource(
    form.withQueryString({
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
    })
  ).then(handleCallCreated)
}

async function prepareComponent() {
  fields.value = await getCreateFields(resourceName, {
    viaResource: props.viaResource,
    viaResourceId: props.viaResourceId,
  })
}

prepareComponent()
</script>
