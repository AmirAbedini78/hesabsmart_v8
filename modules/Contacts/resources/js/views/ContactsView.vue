<template>
  <MainLayout :overlay="!componentReady">
    <div class="mx-auto max-w-7xl">
      <IAlert
        v-if="componentReady && !resource.authorizations.view"
        class="mb-6"
        variant="warning"
      >
        <IAlertBody>
          {{ $t('core::role.view_non_authorized_after_record_create') }}
        </IAlertBody>
      </IAlert>

      <div v-if="componentReady" class="relative">
        <ICard>
          <div class="px-3 py-4 sm:p-6">
            <div class="flex grow flex-col lg:flex-row lg:items-center">
              <div
                class="overflow-hidden text-center lg:flex lg:grow lg:items-center lg:space-x-3 lg:text-left"
              >
                <IAvatar
                  class="lg:shrink-0 lg:self-start"
                  size="md"
                  :src="resource.avatar_url"
                  :title="resource.name"
                />

                <div class="space-y-2 overflow-hidden lg:space-y-0">
                  <div
                    class="flex flex-col items-center space-y-1 lg:flex-row lg:space-x-4 lg:space-y-0"
                  >
                    <IPopover
                      v-slot="{ hide }"
                      @show="
                        ;(fullNameForm.first_name = resource.first_name),
                          (fullNameForm.last_name = resource.last_name)
                      "
                      @hide="fullNameForm.errors.clear()"
                    >
                      <IPopoverButton
                        as="button"
                        class="relative rounded-md text-2xl font-bold text-neutral-900 hover:bg-neutral-100 focus:outline-none dark:text-white dark:hover:bg-neutral-800 lg:truncate"
                        :text="resource.display_name"
                        :disabled="!resource.authorizations.update"
                      />

                      <IPopoverPanel class="w-80">
                        <form @submit.prevent="updateFullName">
                          <IPopoverBody>
                            <IFormGroup
                              label-for="editFirstName"
                              :label="
                                $t('contacts::fields.contacts.first_name')
                              "
                              required
                            >
                              <IFormInput
                                id="editFirstName"
                                v-model="fullNameForm.first_name"
                              />

                              <IFormError
                                :error="fullNameForm.getError('first_name')"
                              />
                            </IFormGroup>

                            <IFormGroup
                              label-for="editLastName"
                              :label="$t('contacts::fields.contacts.last_name')"
                            >
                              <IFormInput
                                id="editLastName"
                                v-model="fullNameForm.last_name"
                              />

                              <IFormError
                                :error="fullNameForm.getError('last_name')"
                              />
                            </IFormGroup>
                          </IPopoverBody>

                          <IPopoverFooter class="flex justify-end space-x-2">
                            <IButton
                              :disabled="fullNameForm.busy"
                              :text="$t('core::app.cancel')"
                              basic
                              @click="hide"
                            />

                            <IButton
                              type="submit"
                              variant="primary"
                              :loading="fullNameForm.busy"
                              :disabled="
                                fullNameForm.busy || !fullNameForm.first_name
                              "
                              :text="$t('core::app.save')"
                              @click="updateFullName().then(hide)"
                            />
                          </IPopoverFooter>
                        </form>
                      </IPopoverPanel>
                    </IPopover>

                    <div class="shrink-0">
                      <InputTagsSelect
                        :disabled="!resource.authorizations.update"
                        :type="$scriptConfig('contacts.tags_type')"
                        :model-value="resource.tags"
                        simple
                        @update:model-value="updateResource({ tags: $event })"
                      />
                    </div>
                  </div>

                  <IText
                    v-if="resource.job_title"
                    class="block overflow-hidden truncate"
                  >
                    {{
                      isAssociateToOneCompany
                        ? $t('contacts::contact.works_at', {
                            job_title: resource.job_title,
                            company: resource.companies[0].name,
                          })
                        : resource.job_title
                    }}
                  </IText>
                </div>
              </div>

              <div
                class="flex shrink-0 flex-col items-center lg:ml-6 lg:flex-row lg:space-x-2"
              >
                <IButton
                  v-show="resource.authorizations.update"
                  v-once
                  variant="success"
                  class="mr-3 mt-5 lg:mt-0 lg:shrink-0"
                  icon="PlusSolid"
                  :text="$t('deals::deal.add')"
                  @click="showDealCreateModal = true"
                />

                <div
                  class="mt-5 flex shrink-0 justify-center space-x-0.5 lg:mt-0 lg:items-center lg:justify-normal"
                >
                  <UserOwnerDropdown
                    :owner="resource.user"
                    :authorize-update="resource.authorizations.update"
                    @change="updateResource({ user_id: $event?.id || null })"
                  />

                  <ActionSelector
                    type="dropdown"
                    :ids="resource.id || []"
                    :actions="resource.actions || []"
                    :resource-name="resourceName"
                    @action-executed="handleActionExecuted"
                  />
                </div>
              </div>
            </div>
          </div>
        </ICard>
      </div>

      <div v-if="componentReady" class="mt-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
          <div class="col-span-4">
            <Panels
              v-slot="{ panel }"
              v-model:panels="page.panels"
              :identifier="resourceName"
            >
              <div class="mb-3">
                <component
                  :is="panel.component"
                  :resource-name="resourceName"
                  :resource-id="resource.id"
                  :resource="resource"
                  :panel="panel"
                  @updated="synchronizeResource($event, true)"
                />
              </div>
            </Panels>
          </div>

          <div class="col-span-8 mt-4 lg:mt-0">
            <ITabGroup :default-index="defaultTabIndex">
              <ICard
                class="has-[[data-headlessui-state=selected]:not(:first-child)]:rounded-b-none"
              >
                <ITabList
                  class="has-[[data-headlessui-state=selected]:not(:first-child)]:pb-2.5 has-[[data-headlessui-state=selected]:not(:first-child)]:sm:pb-0"
                  centered
                >
                  <component
                    :is="tabComponents[tab.component] || tab.component"
                    v-for="tab in page.tabs"
                    :key="tab.id"
                    :resource-name="resourceName"
                    :resource-id="resource.id"
                    :resource="resource"
                  />
                </ITabList>
              </ICard>

              <ITabPanels class="[&_[data-slot=panel]]:-mt-[18px]">
                <component
                  :is="tabComponents[tab.panelComponent] || tab.panelComponent"
                  v-for="tab in page.tabs"
                  :id="'tabPanel-' + tab.id"
                  :key="tab.id"
                  scroll-element="#main"
                  :resource-name="resourceName"
                  :resource-id="resource.id"
                  :resource="resource"
                />
              </ITabPanels>
            </ITabGroup>
          </div>
        </div>
      </div>
    </div>

    <DealsCreate
      v-if="showDealCreateModal"
      :via-resource="resourceName"
      :parent-resource="resource"
      :go-to-list="false"
      @associated="fetchResource(), (showDealCreateModal = false)"
      @created="
        ({ isRegularAction }) => (
          isRegularAction ? (showDealCreateModal = false) : '', fetchResource()
        )
      "
      @hidden="showDealCreateModal = false"
    />
  </MainLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, provide, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import ActionSelector from '@/Core/components/Actions/ActionSelector.vue'
import InputTagsSelect from '@/Core/components/InputTagsSelect.vue'
import { usePrivateChannel } from '@/Core/composables/useBroadcast'
import { useForm } from '@/Core/composables/useForm'
import { useGlobalEventListener } from '@/Core/composables/useGlobalEventListener'
import { usePageTitle } from '@/Core/composables/usePageTitle'
import { useResource } from '@/Core/composables/useResource'
import TimelineTab from '@/Core/views/Timeline/RecordTabTimeline.vue'
import TimelineTabPanel from '@/Core/views/Timeline/RecordTabTimelinePanel.vue'

import DealsCreate from '@/Deals/views/DealsCreate.vue'
import UserOwnerDropdown from '@/Users/components/UserOwnerDropdown.vue'

const tabComponents = {
  'timeline-tab': TimelineTab,
  'timeline-tab-panel': TimelineTabPanel,
}

const resourceName = Innoclapps.resourceName('contacts')

const router = useRouter()
const route = useRoute()
const { form: fullNameForm } = useForm({ first_name: null, last_name: null })

const contactId = computed(() => route.params.id)

const {
  resourceInformation,
  resource,
  synchronizeResource,
  detachResourceAssociations,
  incrementResourceCount,
  decrementResourceCount,
  fetchResource,
  updateResource,
  resourceReady: componentReady,
} = useResource(resourceName, contactId)

const showDealCreateModal = ref(false)

const page = ref(resourceInformation.value.detailPage)

const defaultTabIndex = route.query.section
  ? page.value.tabs.findIndex(tab => tab.id === route.query.section)
  : 0

const broadcastChannel = computed(() =>
  resource.value?.authorizations?.view
    ? `Modules.Contacts.Models.Contact.${contactId.value}`
    : null
)

provide('fetchResource', fetchResource)
provide('synchronizeResource', synchronizeResource)
provide('detachResourceAssociations', detachResourceAssociations)
provide('incrementResourceCount', incrementResourceCount)
provide('decrementResourceCount', decrementResourceCount)

usePageTitle(computed(() => resource.value.display_name))

const { stopListening } = usePrivateChannel(
  broadcastChannel,
  '.ContactUpdated',
  () => fetchResource()
)

onBeforeUnmount(stopListening)

useGlobalEventListener('refresh-details-view', () => fetchResource())

useGlobalEventListener('floating-resource-updated', () => fetchResource())

const isAssociateToOneCompany = computed(
  () => resource.value.companies.length == 1
)

function handleActionExecuted(action) {
  if (!action.destroyable) {
    fetchResource()
  } else {
    router.push({ name: 'contact-index' })
  }
}

function updateFullName() {
  return updateResource(fullNameForm)
}

fetchResource()
</script>
