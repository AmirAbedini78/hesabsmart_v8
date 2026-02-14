<template>
  <ICard v-show="!callBeingEdited" v-bind="$attrs" :class="'call-' + callId">
    <ICardHeader>
      <div class="flex w-full flex-1">
        <div class="mr-2 mt-px flex shrink-0 self-start">
          <IAvatar :src="user.avatar_url" />
        </div>

        <div
          class="flex grow flex-col space-y-1 lg:flex-row lg:space-x-3 lg:space-y-0"
        >
          <div class="flex grow flex-col items-start">
            <ITextBlockDark class="mt-0.5 grow lg:mt-0.5">
              <I18nT scope="global" keypath="calls::call.info_created">
                <template #user>
                  <span class="font-medium" v-text="user.name" />
                </template>

                <template #date>
                  <span
                    class="font-medium"
                    v-text="localizedDateTime(callDate)"
                  />
                </template>
              </I18nT>
            </ITextBlockDark>

            <AssociationsPopover
              placement="bottom-start"
              :associations-count="associationsCount"
              :initial-associateables="relatedResource"
              :resource-id="callId"
              :resource-name="resourceName"
              :primary-record="relatedResource"
              :primary-resource-name="viaResource"
              @synced="synchronizeResource({ calls: $event })"
            />
          </div>

          <div class="space-x-2 lg:space-x-0">
            <ITextDark
              class="inline font-medium lg:hidden"
              :text="$t('calls::call.outcome.outcome')"
            />

            <IDropdown>
              <IDropdownButton
                class="lg:mt-1.5"
                :as="IBadgeButton"
                :disabled="!authorizations.update"
                :color="outcome.swatch_color"
              >
                <span class="truncate lg:max-w-36" v-text="outcome.name" />
              </IDropdownButton>

              <IDropdownMenu>
                <IDropdownItem
                  v-for="callOutcome in outcomesByName"
                  :key="callOutcome.id"
                  :text="callOutcome.name"
                  :active="outcomeId === callOutcome.id"
                  @click="update({ call_outcome_id: callOutcome.id })"
                />
              </IDropdownMenu>
            </IDropdown>
          </div>
        </div>
      </div>

      <ICardActions class="mt-1 self-start">
        <IDropdownMinimal
          v-if="authorizations.update && authorizations.delete"
          small
        >
          <IDropdownItem
            v-show="authorizations.update"
            :text="$t('core::app.edit')"
            @click="toggleEdit"
          />

          <IDropdownItem
            v-show="authorizations.delete"
            :text="$t('core::app.delete')"
            @click="$confirm(() => destroy(callId))"
          />
        </IDropdownMinimal>
      </ICardActions>
    </ICardHeader>

    <ICardBody>
      <EditorText @dblclick="toggleEdit">
        <TextCollapse v-if="collapsable" :text="body" :length="250" lightbox />

        <HtmlableLightbox v-else :html="body" />
      </EditorText>

      <CollapsableCommentsList
        v-slot="{
          hasComments,
          totalComments,
          commentsAreBeingLoaded,
          toggleCommentsVisibility,
        }"
        class="mt-3"
        commentable-type="calls"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :commentable-id="callId"
        :count="commentsCount"
        :comments="comments"
        @updated="
          synchronizeResource({
            calls: { id: callId, comments: $event },
          })
        "
        @deleted="
          synchronizeResource({
            calls: { id: callId, comments: { id: $event, _delete: true } },
          })
        "
        @update:comments="
          synchronizeResource({
            calls: { id: callId, comments: $event },
          })
        "
        @update:count="
          synchronizeResource({
            calls: { id: callId, comments_count: $event },
          })
        "
      >
        <CollapseableCommentsLink
          v-if="hasComments"
          class="mt-6"
          :loading="commentsAreBeingLoaded"
          :total="totalComments"
          :collapsed="commentsAreVisible"
          @click="toggleCommentsVisibility"
        />
      </CollapsableCommentsList>
    </ICardBody>

    <ICardFooter class="text-right">
      <CommentsAdd
        commentable-type="calls"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :commentable-id="callId"
        @created="
          (commentsAreVisible = true),
            synchronizeResource({
              calls: {
                id: callId,
                comments: [$event],
              },
            })
        "
      />
    </ICardFooter>
  </ICard>

  <CallsEdit
    v-if="callBeingEdited"
    :via-resource="viaResource"
    :via-resource-id="viaResourceId"
    :call-id="callId"
    @cancelled="callBeingEdited = false"
    @updated="callBeingEdited = false"
  />
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import AssociationsPopover from '@/Core/components/AssociationsPopover.vue'
import HtmlableLightbox from '@/Core/components/Lightbox/HtmlableLightbox.vue'
import { IBadgeButton } from '@/Core/components/UI/Badge'
import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import { useResourceable } from '@/Core/composables/useResourceable'

import { useComments } from '@/Comments/composables/useComments'

import { useCallOutcomes } from '../composables/useCallOutcomes'

import CallsEdit from './CallsEdit.vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  callId: { required: true, type: Number },
  commentsCount: { required: true, type: Number },
  callDate: { required: true, type: String },
  body: { required: true, type: String },
  userId: { required: true, type: Number },
  outcomeId: { required: true, type: Number },
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
  authorizations: { required: true, type: Object },
  associationsCount: { required: true, type: Number },
  relatedResource: { required: true, type: Object },
  comments: { required: true, type: Array },
  collapsable: Boolean,
})

const synchronizeResource = inject('synchronizeResource')
const decrementResourceCount = inject('decrementResourceCount')

const { t } = useI18n()
const { localizedDateTime } = useDates()
const { outcomesByName } = useCallOutcomes()
const { findUserById } = useApp()

const resourceName = Innoclapps.resourceName('calls')

const { updateResource, deleteResource } = useResourceable(resourceName)

const outcome = computed(() =>
  outcomesByName.value.find(o => o.id == props.outcomeId)
)

const user = computed(() => findUserById(props.userId))

const { commentsAreVisible } = useComments(props.callId, 'calls')

const callBeingEdited = ref(false)

async function update(payload = {}) {
  let call = await updateResource(
    {
      call_outcome_id: props.outcomeId,
      date: props.callDate,
      body: props.body,
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
      ...payload,
    },
    props.callId
  )

  synchronizeResource({ calls: call })
}

async function destroy(id) {
  await deleteResource(id)

  synchronizeResource({ calls: { id, _delete: true } })
  decrementResourceCount('calls_count')

  Innoclapps.success(t('calls::call.deleted'))
}

function toggleEdit(e) {
  // The double click to edit should not work while in edit mode
  if (e.type == 'dblclick' && callBeingEdited.value) return
  // For double click event
  if (!props.authorizations.update) return

  callBeingEdited.value = !callBeingEdited.value
}
</script>
