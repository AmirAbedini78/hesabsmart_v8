<template>
  <ICardHeader>
    <div class="flex items-center">
      <Icon
        v-if="
          isConfigured && componentReady && !numbersRetrievalRequestInProgress
        "
        :icon="
          numbers.length === 0 ||
          selectedNumberHasNoVoiceCapabilities ||
          !isSecure
            ? 'XCircleSolid'
            : 'CheckCircle'
        "
        :class="[
          'mr-1 size-5',
          numbers.length === 0 ||
          selectedNumberHasNoVoiceCapabilities ||
          !isSecure
            ? 'text-danger-500'
            : 'text-success-600',
        ]"
      />

      <ICardHeading>{{ $t('calls::twilio.twilio') }}</ICardHeading>

      <IStepsCircle class="pointer-events-none ml-4">
        <IStepCircle :status="!showNumberConfig ? 'current' : 'complete'" />

        <IStepCircle :status="form.twilio_number ? 'complete' : ''" />

        <IStepCircle
          :status="isConfigured && form.twilio_number ? 'complete' : ''"
          is-last
        />
      </IStepsCircle>
    </div>

    <ICardActions>
      <IButton
        v-show="isConfigured"
        variant="danger"
        :text="$t('calls::twilio.disconnect')"
        @click="disconnect"
      />
    </ICardActions>
  </ICardHeader>

  <ICard :overlay="!componentReady">
    <ICardBody>
      <div class="lg:flex lg:space-y-4">
        <div class="w-full">
          <IAlert
            v-slot="{ variant }"
            class="mb-10"
            variant="warning"
            :show="showAppUrlWarning"
          >
            <IAlertBody>
              {{ $t('calls::twilio.app_url_warning') }}
            </IAlertBody>

            <IAlertActions>
              <IButton
                text="Update URL"
                :variant="variant"
                ghost
                @click="updateTwiMLAppURL"
              />
            </IAlertActions>
          </IAlert>

          <IAlert class="mb-10" variant="warning" :show="!isSecure">
            <IAlertBody>
              {{ $t('calls::twilio.https_alert') }}
            </IAlertBody>
          </IAlert>

          <div class="grid grid-cols-12 gap-2 lg:gap-4">
            <div class="col-span-12 lg:col-span-6">
              <IFormGroup>
                <template #label>
                  <div class="mb-1 flex">
                    <div class="grow">
                      <IFormLabel for="twilio_account_sid">
                        {{ $t('calls::twilio.account_sid') }}
                      </IFormLabel>
                    </div>

                    <ILink href="https://www.twilio.com/console">
                      https://www.twilio.com/console
                    </ILink>
                  </div>
                </template>

                <IFormInput
                  id="twilio_account_sid"
                  v-model="form.twilio_account_sid"
                  autocomplete="off"
                />
              </IFormGroup>
            </div>

            <div class="col-span-12 lg:col-span-6">
              <IFormGroup>
                <template #label>
                  <div class="mb-1 flex">
                    <div class="grow">
                      <IFormLabel for="twilio_auth_token">
                        {{ $t('calls::twilio.auth_token') }}
                      </IFormLabel>
                    </div>

                    <ILink href="https://www.twilio.com/console">
                      https://www.twilio.com/console
                    </ILink>
                  </div>
                </template>

                <IFormInput
                  id="twilio_auth_token"
                  v-model="form.twilio_auth_token"
                  type="password"
                  autocomplete="off"
                />
              </IFormGroup>
            </div>
          </div>

          <div
            class="mt-2 border-t border-neutral-200 pt-5 dark:border-neutral-500/30"
            :class="{
              'pointer-events-none opacity-50 blur-sm': !showNumberConfig,
            }"
          >
            <IFormLabel :label="$t('calls::twilio.number')" />

            <IAlert
              class="my-3"
              variant="danger"
              :show="selectedNumberHasNoVoiceCapabilities"
            >
              <IAlertBody>
                {{ $t('calls::twilio.no_voice_capabilities') }}
              </IAlertBody>
            </IAlert>

            <div class="mt-1 flex space-x-2">
              <div class="relative flex grow rounded-lg shadow-sm">
                <div
                  class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                >
                  <Icon
                    icon="Phone"
                    class="size-5 text-neutral-500 dark:text-neutral-300"
                  />
                </div>

                <IFormSelect
                  v-model="form.twilio_number"
                  class="!pl-11"
                  :disabled="!numbers.length"
                >
                  <option value=""></option>

                  <option
                    v-for="number in numbers"
                    :key="number.phoneNumber"
                    :value="number.phoneNumber"
                  >
                    {{ number.phoneNumber }}
                  </option>
                </IFormSelect>
              </div>

              <IButton
                variant="secondary"
                :loading="numbersRetrievalRequestInProgress"
                :disabled="numbersRetrievalRequestInProgress"
                :text="$t('calls::twilio.retrieve_numbers')"
                @click="retrieveNumbers"
              />
            </div>
          </div>

          <div
            class="mt-5 border-t border-neutral-200 pt-5 dark:border-neutral-500/30"
            :class="{
              'pointer-events-none opacity-50 blur-sm': !showIVRConfig,
            }"
          >
            <div class="mb-4 flex items-center justify-between">
              <IFormLabel :label="$t('calls::twilio.ivr_settings')" />

              <IFormSwitchField>
                <IFormSwitchLabel :text="$t('calls::twilio.enable_ivr')" />

                <IFormSwitch v-model="form.twilio_ivr_enabled" />
              </IFormSwitchField>
            </div>

            <div v-if="form.twilio_ivr_enabled" class="space-y-6">
              <!-- Business Hours Configuration -->
              <div class="mb-4 flex items-center justify-between">
                <h4
                  class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
                >
                  {{ $t('calls::twilio.business_hours') }}
                </h4>

                <IFormSwitchField>
                  <IFormSwitchLabel
                    :text="$t('calls::twilio.enable_business_hours')"
                  />

                  <IFormSwitch v-model="form.twilio_business_hours_enabled" />
                </IFormSwitchField>
              </div>

              <div
                v-if="form.twilio_business_hours_enabled"
                class="!mt-3 rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-white/10 dark:bg-neutral-800/40"
              >
                <div class="space-y-4">
                  <!-- Timezone Selection -->
                  <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 lg:col-span-6">
                      <IFormGroup
                        label-for="timezone"
                        :label="$t('calls::twilio.timezone')"
                      >
                        <IFormSelect
                          id="timezone"
                          v-model="form.twilio_timezone"
                        >
                          <option value="America/New_York">
                            Eastern Time (ET)
                          </option>

                          <option value="America/Chicago">
                            Central Time (CT)
                          </option>

                          <option value="America/Denver">
                            Mountain Time (MT)
                          </option>

                          <option value="America/Los_Angeles">
                            Pacific Time (PT)
                          </option>

                          <option value="Europe/London">London (GMT)</option>

                          <option value="Europe/Berlin">Berlin (CET)</option>

                          <option value="Asia/Tokyo">Tokyo (JST)</option>

                          <option value="Australia/Sydney">
                            Sydney (AEDT)
                          </option>
                        </IFormSelect>
                      </IFormGroup>
                    </div>
                  </div>

                  <!-- Days and Hours -->
                  <div class="grid grid-cols-1 gap-3">
                    <div
                      v-for="day in weekDays"
                      :key="day"
                      class="flex items-center space-x-4 rounded-lg border border-neutral-300 bg-white p-3 dark:border-neutral-600 dark:bg-neutral-700"
                    >
                      <div class="w-32">
                        <IFormSwitchField>
                          <IFormSwitchLabel
                            class="font-medium"
                            :text="$t('core::app.weekdays.' + day)"
                          />

                          <IFormSwitch
                            v-model="form[`twilio_${day}_enabled`]"
                          />
                        </IFormSwitchField>
                      </div>

                      <div
                        v-if="form[`twilio_${day}_enabled`]"
                        class="flex flex-grow items-center space-x-2"
                      >
                        <IFormInput
                          v-model="form[`twilio_${day}_start`]"
                          type="time"
                          class="w-36"
                        />

                        <span class="text-neutral-500">to</span>

                        <IFormInput
                          v-model="form[`twilio_${day}_end`]"
                          type="time"
                          class="w-36"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h4
                  class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
                >
                  {{ $t('calls::twilio.ivr_messages') }}
                </h4>

                <div
                  class="mt-1 text-sm text-neutral-500 dark:text-neutral-300"
                >
                  {{ $t('calls::twilio.message_tip') }}
                </div>
              </div>
              <!-- IVR Messages Configuration -->
              <div
                class="!mt-3 rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-white/10 dark:bg-neutral-800/40"
              >
                <div class="mt-4 space-y-4">
                  <IFormGroup
                    label-for="greeting_message"
                    :label="$t('calls::twilio.greeting_message')"
                  >
                    <IFormTextarea
                      id="greeting_message"
                      v-model="form.twilio_greeting_message"
                      rows="2"
                      :placeholder="$t('calls::twilio.greeting_placeholder')"
                    />
                  </IFormGroup>

                  <IFormGroup
                    label-for="waiting_message"
                    :label="$t('calls::twilio.waiting_message')"
                  >
                    <IFormTextarea
                      id="waiting_message"
                      v-model="form.twilio_waiting_message"
                      rows="2"
                      :placeholder="$t('calls::twilio.waiting_placeholder')"
                    />
                  </IFormGroup>

                  <IFormGroup
                    v-if="form.twilio_business_hours_enabled"
                    label-for="out_of_hours_message"
                    :label="$t('calls::twilio.out_of_hours_message')"
                  >
                    <IFormTextarea
                      id="out_of_hours_message"
                      v-model="form.twilio_out_of_hours_message"
                      rows="3"
                      :placeholder="
                        $t('calls::twilio.out_of_hours_placeholder')
                      "
                    />
                  </IFormGroup>
                </div>
              </div>

              <!-- Hold Music Configuration -->
              <h4
                class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
              >
                {{ $t('calls::twilio.hold_music') }}
              </h4>

              <div
                class="!mt-3 rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-white/10 dark:bg-neutral-800/40"
              >
                <div class="space-y-4">
                  <IFormGroup
                    label-for="hold_music_url"
                    :label="$t('calls::twilio.hold_music_url')"
                    :description="$t('calls::twilio.audio_requirements')"
                  >
                    <div class="flex space-x-2">
                      <IFormInput
                        id="hold_music_url"
                        v-model="form.twilio_hold_music_url"
                        class="flex-grow"
                        :placeholder="
                          $t('calls::twilio.hold_music_placeholder')
                        "
                      />

                      <IButton
                        variant="secondary"
                        class="shrink-0"
                        icon="Play"
                        :text="$t('calls::twilio.test_audio')"
                        :disabled="!form.twilio_hold_music_url"
                        @click="testAudioUrl"
                      />
                    </div>
                  </IFormGroup>

                  <!-- Additional Audio URLs -->
                  <div
                    class="border-t border-neutral-200 pt-4 dark:border-neutral-700"
                  >
                    <IFormLabel>{{
                      $t('calls::twilio.additional_audio_sources')
                    }}</IFormLabel>

                    <div
                      class="mt-2 space-y-2 text-sm text-neutral-600 dark:text-neutral-400"
                    >
                      <div>
                        <strong>{{ $t('calls::twilio.free_sources') }}:</strong>
                      </div>

                      <ul class="space-y-1">
                        <li>
                          <ILink
                            href="https://freesound.org"
                            target="_blank"
                            rel="noopener noreferrer"
                          >
                            Freesound.org
                          </ILink>
                          - {{ $t('calls::twilio.royalty_free_sounds') }}
                        </li>

                        <li>
                          <ILink
                            href="https://zapsplat.com"
                            target="_blank"
                            rel="noopener noreferrer"
                          >
                            ZapSplat.com
                          </ILink>
                          - {{ $t('calls::twilio.professional_audio') }}
                        </li>

                        <li>
                          <ILink
                            href="https://incompetech.com"
                            target="_blank"
                            rel="noopener noreferrer"
                          >
                            Incompetech.com
                          </ILink>
                          - {{ $t('calls::twilio.background_music') }}
                        </li>
                      </ul>

                      <IAlert class="mt-3" variant="info">
                        <IAlertHeading>
                          {{ $t('calls::twilio.tip') }}:
                        </IAlertHeading>

                        <IAlertBody>
                          {{ $t('calls::twilio.url_tip') }}
                        </IAlertBody>
                      </IAlert>

                      <IAlert class="mt-2" variant="warning">
                        <IAlertHeading>
                          {{ $t('calls::twilio.cors_note') }}:
                        </IAlertHeading>

                        <IAlertBody>
                          {{ $t('calls::twilio.cors_explanation') }}
                        </IAlertBody>
                      </IAlert>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Advanced Settings -->
              <h4
                class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
              >
                {{ $t('calls::twilio.advanced_settings') }}
              </h4>

              <div
                class="!mt-3 rounded-lg border border-neutral-200 bg-neutral-50 p-4 dark:border-white/10 dark:bg-neutral-800/40"
              >
                <div class="grid grid-cols-12 gap-4">
                  <div class="col-span-12 lg:col-span-6">
                    <IFormGroup
                      label-for="waiting_loop_duration"
                      :label="$t('calls::twilio.waiting_loop_duration')"
                    >
                      <div class="flex items-center space-x-2">
                        <IFormInput
                          id="waiting_loop_duration"
                          v-model.number="form.twilio_waiting_loop_duration"
                          type="number"
                          min="10"
                          max="120"
                          class="w-24"
                        />

                        <span
                          class="text-sm text-neutral-500 dark:text-neutral-200"
                        >
                          {{ $t('calls::twilio.seconds') }}
                        </span>
                      </div>

                      <div
                        class="mt-1 text-xs text-neutral-500 dark:text-neutral-300"
                      >
                        {{ $t('calls::twilio.loop_duration_tip') }}
                      </div>
                    </IFormGroup>
                  </div>
                </div>

                <div class="mt-4 grid grid-cols-12 gap-4">
                  <div class="col-span-12 lg:col-span-6">
                    <IFormGroup
                      label-for="voice_language"
                      :label="$t('calls::twilio.voice_language')"
                    >
                      <IFormSelect
                        id="voice_language"
                        v-model="form.twilio_voice_language"
                      >
                        <option value="en-US">English (US)</option>

                        <option value="en-GB">English (UK)</option>

                        <option value="es-ES">Spanish (Spain)</option>

                        <option value="es-MX">Spanish (Mexico)</option>

                        <option value="fr-FR">French</option>

                        <option value="de-DE">German</option>

                        <option value="it-IT">Italian</option>

                        <option value="pt-BR">Portuguese (Brazil)</option>
                      </IFormSelect>
                    </IFormGroup>
                  </div>

                  <div class="col-span-12 lg:col-span-6">
                    <IFormGroup
                      label-for="voice_type"
                      :label="$t('calls::twilio.voice_type')"
                    >
                      <IFormSelect
                        id="voice_type"
                        v-model="form.twilio_voice_type"
                      >
                        <option value="alice">
                          Alice (Clear, Professional)
                        </option>

                        <option value="man">Man (Deep Voice)</option>

                        <option value="woman">Woman (Warm Voice)</option>
                      </IFormSelect>
                    </IFormGroup>
                  </div>
                </div>
              </div>

              <!-- Preview Section -->
              <h4 class="text-sm font-medium text-info-900 dark:text-info-100">
                {{ $t('calls::twilio.call_flow_preview') }}
              </h4>

              <div
                class="!mt-3 rounded-lg border border-info-200 bg-info-50 p-4 dark:border-info-800/30 dark:bg-info-900/10"
              >
                <div class="space-y-1 text-sm text-info-800 dark:text-info-200">
                  <div class="flex items-center space-x-2">
                    <Icon icon="Phone" class="size-4" />

                    <span>{{ $t('calls::twilio.step_1') }}</span>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Icon icon="SpeakerWave" class="size-4" />

                    <span>{{ $t('calls::twilio.step_2') }}</span>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Icon icon="MusicalNote" class="size-4" />

                    <span>{{ $t('calls::twilio.step_3') }}</span>
                  </div>

                  <div class="flex items-center space-x-2">
                    <Icon icon="Users" class="size-4" />

                    <span>{{ $t('calls::twilio.step_4') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            class="mt-5 border-t border-neutral-200 pt-5 dark:border-neutral-500/30"
            :class="{
              'pointer-events-none opacity-50 blur-sm': !showTwilioAppConfig,
            }"
          >
            <IFormLabel :label="$t('calls::twilio.app')" />

            <div class="mt-1 flex space-x-2">
              <IFormInput
                v-model="form.twilio_app_sid"
                class="grow"
                :disabled="true"
              />

              <IButton
                variant="primary"
                class="shrink-0"
                :loading="appIsBeingCreated"
                :disabled="
                  appIsBeingCreated ||
                  hasAppSid ||
                  selectedNumberHasNoVoiceCapabilities
                "
                :text="$t('calls::twilio.create_app')"
                @click="createTwiMLApp"
              />

              <IButton
                v-if="hasAppSid"
                class="shrink-0"
                icon="Trash"
                basic
                @click="deleteTwiMLApp"
              />
            </div>
          </div>
        </div>
      </div>
    </ICardBody>

    <ICardFooter v-if="isConfigured || twimlAppDeleted" class="text-right">
      <IButton
        variant="primary"
        :disabled="selectedNumberHasNoVoiceCapabilities"
        :text="$t('core::app.save')"
        @click="save"
      />
    </ICardFooter>
  </ICard>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import find from 'lodash/find'

import {
  IFormSwitch,
  IFormSwitchField,
  IFormSwitchLabel,
} from '@/Core/components/UI/Form/Switch'
import { useApp } from '@/Core/composables/useApp'
import { useForm } from '@/Core/composables/useForm'
import { isBlank } from '@/Core/utils'

const { t } = useI18n()
const { form } = useForm()
const { scriptConfig } = useApp()

const numbers = ref([])
const componentReady = ref(false)
const appIsBeingCreated = ref(false)
const twimlAppDeleted = ref(false)
const numbersRetrievalRequestInProgress = ref(false)
const showAppUrlWarning = ref(false)

const isSecure = scriptConfig('is_secure')

const hasAuthToken = computed(() => !isBlank(form.twilio_auth_token))

const hasAccountSid = computed(() => !isBlank(form.twilio_account_sid))

const hasAppSid = computed(() => !isBlank(form.twilio_app_sid))

const showNumberConfig = computed(
  () => hasAuthToken.value && hasAccountSid.value
)

// Add this computed property to your existing ones
const showIVRConfig = computed(() => isConfigured.value && form.twilio_number)

const showTwilioAppConfig = computed(() => !isBlank(form.twilio_number))

const isConfigured = computed(
  () => hasAuthToken.value && hasAccountSid.value && hasAppSid.value
)

const selectedNumber = computed(() =>
  find(numbers.value, ['phoneNumber', form.twilio_number])
)

const selectedNumberHasNoVoiceCapabilities = computed(() => {
  if (!selectedNumber.value) {
    return false
  }

  return selectedNumber.value.capabilities.voice === false
})

// Business days configuration
const weekDays = [
  'monday',
  'tuesday',
  'wednesday',
  'thursday',
  'friday',
  'saturday',
  'sunday',
]

// Add these methods to your existing methods
function testAudioUrl() {
  if (!form.twilio_hold_music_url) {
    return
  }

  const url = form.twilio_hold_music_url

  // First, try to validate the URL format
  try {
    new URL(url)
  } catch {
    Innoclapps.error(t('calls::twilio.invalid_url_format'))

    return
  }

  // Test if the URL is accessible by making a HEAD request
  testUrlAccessibility(url)
    .then(() => {
      // If accessible, try to play it
      const audio = new Audio(url)

      // Set up event listeners
      audio.addEventListener('canplaythrough', () => {
        Innoclapps.success(t('calls::twilio.audio_test_success'))
        audio.pause() // Stop playing after successful test
      })

      audio.addEventListener('error', e => {
        console.error('Audio error:', e)
        handleAudioError(e.target.error, url)
      })

      // Try to load and play
      audio.load()

      audio.play().catch(error => {
        console.error('Play error:', error)
        handlePlayError(error, url)
      })
    })
    .catch(error => {
      console.error('URL accessibility error:', error)
      Innoclapps.error(t('calls::twilio.url_not_accessible'))
    })
}

function testUrlAccessibility(url) {
  return new Promise((resolve, reject) => {
    // Use backend endpoint to test URL accessibility
    Innoclapps.request()
      .post('/twilio/test-audio-url', { url })
      .then(({ data }) => {
        if (data.valid) {
          resolve(data)
        } else {
          reject(new Error(data.message || 'URL not accessible'))
        }
      })
      .catch(reject)
  })
}

function handleAudioError(error, url) {
  console.error('Audio error details:', error)

  switch (error?.code) {
    case 1: // MEDIA_ERR_ABORTED
      Innoclapps.error(t('calls::twilio.audio_aborted'))
      break
    case 2: // MEDIA_ERR_NETWORK
      Innoclapps.error(t('calls::twilio.audio_network_error'))
      break
    case 3: // MEDIA_ERR_DECODE
      Innoclapps.error(t('calls::twilio.audio_decode_error'))
      break
    case 4: // MEDIA_ERR_SRC_NOT_SUPPORTED
      showCorsErrorMessage(url)
      break
    default:
      Innoclapps.error(t('calls::twilio.audio_unknown_error'))
  }
}

function handlePlayError(error, url) {
  console.error('Play error details:', error)

  if (error.name === 'NotSupportedError' || error.name === 'NotAllowedError') {
    showCorsErrorMessage(url)
  } else {
    Innoclapps.error(t('calls::twilio.audio_play_failed'))
  }
}

function showCorsErrorMessage(url) {
  const domain = new URL(url).hostname

  Innoclapps.error(
    t('calls::twilio.cors_error', { domain }) +
      ' ' +
      t('calls::twilio.cors_solution')
  )
}

function save() {
  if (!form.twilio_account_sid && !form.twilio_auth_token) {
    form.twilio_number = null
  }

  // Use the new Twilio settings endpoint
  form
    .post('/twilio/settings')
    .then(() => {
      Innoclapps.success(t('core::settings.updated'))
      window.location.reload()
    })
    .catch(error => {
      if (error.response?.data?.errors) {
        // Handle validation errors
        Object.keys(error.response.data.errors).forEach(field => {
          const messages = error.response.data.errors[field]

          messages.forEach(message => {
            Innoclapps.error(message)
          })
        })
      } else {
        Innoclapps.error(t('core::app.something_went_wrong'))
      }
    })
}

function disconnect() {
  Innoclapps.request()
    .delete('/twilio')
    .then(() => {
      window.location.reload()
    })
}

function updateTwiMLAppURL() {
  Innoclapps.request()
    .put(`/twilio/app/${form.twilio_app_sid}`, {
      voiceUrl: scriptConfig('voip.endpoints.call'),
    })
    .then(() => {
      window.location.reload()
    })
}

function retrieveNumbers() {
  numbersRetrievalRequestInProgress.value = true

  Innoclapps.request('/twilio/numbers', {
    params: {
      account_sid: form.twilio_account_sid,
      auth_token: form.twilio_auth_token,
    },
  })
    .then(({ data }) => {
      numbers.value = data
    })
    .finally(() => (numbersRetrievalRequestInProgress.value = false))
}

/**
 * Get the TwiML app associated with the integration
 *
 * @return {Object}
 */
async function getTwiMLApp() {
  let { data } = await Innoclapps.request(`/twilio/app/${form.twilio_app_sid}`)

  return data
}

function createTwiMLApp() {
  appIsBeingCreated.value = true

  Innoclapps.request()
    .post('/twilio/app', {
      number: form.twilio_number,
      account_sid: form.twilio_account_sid,
      auth_token: form.twilio_auth_token,
      voiceMethod: 'POST',
      voiceUrl: scriptConfig('voip.endpoints.call'),
      friendlyName: 'Concord CRM',
    })
    .then(({ data }) => {
      form.twilio_app_sid = data.app_sid
    })
    .finally(() => (appIsBeingCreated.value = false))
}

function deleteTwiMLApp() {
  Innoclapps.confirm().then(deleteTwiMLAppWithoutConfirmation)
}

function deleteTwiMLAppWithoutConfirmation() {
  Innoclapps.request()
    .delete(`/twilio/app/${form.twilio_app_sid}`, {
      params: {
        account_sid: form.twilio_account_sid,
        auth_token: form.twilio_auth_token,
      },
    })
    .then(() => {
      twimlAppDeleted.value = true
      form.twilio_app_sid = null
    })
}

function prepareComponent() {
  Innoclapps.request('/twilio/settings').then(({ data }) => {
    form.set({
      ...data,
    })

    componentReady.value = true

    if (hasAuthToken.value && hasAccountSid.value) {
      retrieveNumbers()

      if (hasAppSid.value) {
        getTwiMLApp()
          .then(app => {
            if (app.voiceUrl !== scriptConfig('voip.endpoints.call')) {
              showAppUrlWarning.value = true
            }
          })
          .catch(e => {
            if (e.response.data.deleted) {
              deleteTwiMLAppWithoutConfirmation()
            } else {
              console.error(e)
            }
          })
      }
    }
  })
}

prepareComponent()
</script>
