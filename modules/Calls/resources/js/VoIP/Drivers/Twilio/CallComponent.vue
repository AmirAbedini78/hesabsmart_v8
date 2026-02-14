<template>
  <IModal
    id="callingActivation"
    cancel-variant="success"
    :cancel-text="$t('calls::call.activate_voip')"
    :title="$t('calls::call.activation_required')"
    @hidden="callingModalHiddenEvent"
  >
    <!-- Removes the ok button by providing an empty slot -->
    <template #modal-ok>&nbsp;</template>

    <ITextDark>
      {{
        $t('calls::call.activation_gesture_required', {
          askForActivationIn: askForActivationIn,
        })
      }}
    </ITextDark>
  </IModal>

  <div
    v-show="showCallComponent"
    class="absolute inset-x-0 bottom-0 z-50 sm:inset-auto sm:bottom-10 sm:right-10 sm:mx-4"
  >
    <div
      class="w-full overflow-hidden rounded-lg border border-neutral-400/60 bg-white shadow-2xl dark:border-neutral-700 dark:bg-neutral-900 sm:w-[400px] sm:max-w-[450px]"
    >
      <div
        class="border-b border-neutral-300 bg-neutral-100 px-6 py-2 dark:border-neutral-700 dark:bg-neutral-800"
      >
        <div class="flex items-center justify-between">
          <h2 class="font-bold text-neutral-900 dark:text-white">
            {{ $t('calls::call.call') }}
          </h2>

          <div>
            <IButton
              v-show="visible === true"
              v-i-tooltip.left="$t('calls::call.hide_bar')"
              class="-mr-3"
              icon="XSolid"
              basic
              @click="hideCallHandler"
            />
          </div>
        </div>
      </div>

      <div class="px-6 py-5">
        <IAlert
          class="mb-3"
          variant="danger"
          :show="error !== null"
          dismissible
          @dismissed="error = null"
        >
          <IAlertBody>{{ error }}</IAlertBody>
        </IAlert>

        <div class="flex items-center space-x-4">
          <p
            v-if="!person.resourceName"
            class="text-base font-medium"
            :class="
              isCallInProgress || (isIncoming && !isCallInProgress)
                ? 'text-success-500'
                : 'text-info-800 dark:text-info-100'
            "
            v-text="cardHeader"
          />

          <ILink
            v-else
            class="flex items-center gap-x-2 font-medium underline underline-offset-2"
            :variant="
              isCallInProgress || (isIncoming && !isCallInProgress)
                ? 'success'
                : 'info'
            "
            @click.prevent="
              floatResourceInDetailMode({
                resourceName: person.resourceName,
                resourceId: person.id,
              })
            "
          >
            {{ cardHeader }}
            <Icon icon="Window" class="size-4" />
          </ILink>
        </div>

        <div class="mt-4 flex items-center gap-x-4">
          <IBadge
            v-show="isCallInProgress || (lastConnectedNumber && duration)"
            variant="info"
          >
            <Icon icon="Clock" class="mr-1 size-4" />
            {{ isCallInProgress || lastConnectedNumber ? duration : null }}
          </IBadge>

          <div v-show="isCallInProgress" class="flex space-x-3 pl-2.5">
            <div class="flex items-center">
              <Icon
                v-i-tooltip.bottom="$t('calls::call.speaker_volume')"
                icon="VolumeUp"
                class="size-5 text-current dark:text-neutral-200"
              />

              <div ref="outputVolumeBar" class="ml-1 h-4 rounded-md"></div>
            </div>

            <div class="flex items-center">
              <Icon
                v-i-tooltip.bottom="$t('calls::call.mic_volume')"
                icon="Microphone"
                class="h-4 w-5 text-current dark:text-neutral-200"
              />

              <div ref="inputVolumeBar" class="ml-1 h-4 rounded-md"></div>
            </div>
          </div>
        </div>

        <div
          class="relative mt-4 flex flex-col items-center sm:-mx-2 md:flex-row"
        >
          <div class="flex flex-col">
            <IDropdown>
              <IDropdownButton icon="Megaphone" class="sm:mr-16" basic>
                <span
                  class="w-full max-w-[19rem] truncate"
                  v-text="audioDevices.speaker.selected?.label"
                />
              </IDropdownButton>

              <IDropdownMenu>
                <IDropdownItem
                  v-for="speaker in audioDevices.speaker.items"
                  :key="speaker.id"
                  :text="speaker.label"
                  @click="
                    getDevice().instance.audio.speakerDevices.set([speaker.id])
                  "
                />
              </IDropdownMenu>
            </IDropdown>

            <IDropdown>
              <IDropdownButton icon="Microphone" class="sm:mr-16" basic>
                <span
                  class="w-full max-w-[19rem] truncate"
                  v-text="audioDevices.ringtone.selected?.label"
                />
              </IDropdownButton>

              <IDropdownMenu>
                <IDropdownItem
                  v-for="ringtone in audioDevices.ringtone.items"
                  :key="ringtone.id"
                  :text="ringtone.label"
                  @click="
                    getDevice().instance.audio.ringtoneDevices.set([
                      ringtone.id,
                    ])
                  "
                />
              </IDropdownMenu>
            </IDropdown>

            <div class="absolute right-0">
              <IButton
                v-i-tooltip="$t('calls::call.unknown_devices')"
                class="hidden lg:block"
                icon="Refresh"
                basic
                @click="getMediaDevices"
              />
            </div>
          </div>
        </div>

        <CallDialpad
          v-show="isCallInProgress"
          class="mx-auto mt-6 max-w-xs"
          @pressed="$options.call.sendDigits($event)"
        />

        <div class="mx-auto mb-4 mt-6 w-full max-w-xs text-center">
          <div
            :class="[
              'flex space-x-1',
              isIncoming && !isCallInProgress
                ? 'justify-center'
                : 'justify-between',
            ]"
          >
            <div class="space-x-4">
              <IButton
                v-show="isIncoming && !isCallInProgress"
                v-i-tooltip="$t('calls::call.answer')"
                variant="success"
                icon="Phone"
                pill
                @click="$options.call.accept()"
              />

              <IButton
                v-show="isCallInProgress"
                v-i-tooltip="$t('calls::call.hangup')"
                variant="danger"
                icon="XSolid"
                pill
                @click="$options.call.disconnect()"
              />

              <IButton
                v-show="isIncoming && !isCallInProgress"
                v-i-tooltip="$t('calls::call.reject')"
                variant="danger"
                icon="PhoneXMark"
                pill
                @click="$options.call.reject()"
              />
            </div>

            <div class="space-x-1.5">
              <IButton
                v-if="isCallInProgress"
                v-show="isMuted"
                v-i-tooltip="$t('calls::call.unmute')"
                variant="secondary"
                icon="VolumeUp"
                pill
                @click="$options.call.mute(false)"
              />

              <IButton
                v-if="isCallInProgress"
                v-show="!isMuted"
                v-i-tooltip="$t('calls::call.mute')"
                variant="secondary"
                icon="VolumeOff"
                pill
                @click="$options.call.mute(true)"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import throttle from 'lodash/throttle'
import { DateTime } from 'luxon'

import { useFloatingResourceModal } from '@/Core/composables/useFloatingResourceModal'
import { strTruncate } from '@/Core/utils'

import CallDialpad from './CallDialpad.vue'

export default {
  components: {
    CallDialpad,
  },

  setup() {
    const { floatResourceInDetailMode } = useFloatingResourceModal()

    return {
      floatResourceInDetailMode,
    }
  },

  data: () => ({
    error: null,
    isCallInProgress: false,
    callStartedDate: null,
    callEndedDate: null,
    isIncoming: false,
    duration: null,
    durationInterval: null,
    visible: false,
    isMuted: false,
    lastConnectedNumber: null,
    person: {},
    // Minutes
    askForActivationIn: 1,
    audioDevices: {
      speaker: {
        items: [],
        selected: null,
      },

      ringtone: {
        items: [],
        selected: null,
      },
    },
  }),

  computed: {
    /**
     * Indicates whether the call component should be shown
     *
     * @return {Boolean}
     */
    showCallComponent() {
      return this.visible === true || this.isCallInProgress
    },

    /**
     * Get the card header
     *
     * @return {String}
     */
    cardHeader() {
      // Incoming call
      if (this.isIncoming && !this.isCallInProgress) {
        return this.$t('calls::call.new_from', {
          number: this.person.display_name || this.lastConnectedNumber,
        })
      }
      // Already connected call
      else if (this.isCallInProgress) {
        return this.$t('calls::call.connected_with', {
          number: this.person.display_name || this.lastConnectedNumber,
        })
      }
      // Ended call, shows the last connected number
      else if (this.lastConnectedNumber) {
        return this.$t('calls::call.ended', {
          number: this.person.display_name || this.lastConnectedNumber,
        })
      }

      return ''
    },
  },

  watch: {
    lastConnectedNumber: function (newVal) {
      if (newVal) {
        this.findPersonForDisplay(newVal)
      }
    },
  },

  created() {
    document.documentElement.addEventListener(
      'mousedown',
      throttle(this.bootVoIPDevice, 500)
    )

    window.addEventListener('beforeunload', () => {
      this.getDevice() && this.getDevice().unregister()
    })

    // If the user did not clicked anything in 1 minute
    // We will show a modal to activate the calling functionality
    // As most browsers requires user gesture to enable audio/mic
    setTimeout(
      () => {
        if (!this.getDevice()) {
          this.$dialog.show('callingActivation')
        }
      },
      this.askForActivationIn * 60 * 1000
    )
  },

  methods: {
    strTruncate,

    /**
     * Find person to display as a caller
     *
     * @param {String} phoneNumber
     *
     * @return {Void}
     */
    findPersonForDisplay(phoneNumber) {
      let queryString = {
        search_fields: 'phones.number:=',
        q: phoneNumber,
      }

      Promise.all([
        Innoclapps.request('/contacts/search', { params: queryString }),
        Innoclapps.request('/companies/search', { params: queryString }),
      ]).then(values => {
        let contacts = values[0].data
        let companies = values[1].data

        if (contacts.length > 0) {
          this.person = {
            display_name: contacts[0].display_name,
            id: contacts[0].id,
            resourceName: 'contacts',
          }
        } else if (companies.length > 0) {
          this.person = {
            display_name: companies[0].display_name,
            id: companies[0].id,
            resourceName: 'companies',
          }
        } else {
          this.person = {}
        }
      })
    },

    /**
     * Manually hide the call handler cards
     *
     * @return {Void}
     */
    hideCallHandler() {
      this.visible = false
      // Clear the duration so the next time call is connected
      // to not see the previous durection during the call initialization
      this.duration = null
    },

    /**
     * Handle the calling modal hidden event
     *
     * @return {Void}
     */
    callingModalHiddenEvent() {
      Innoclapps.success(this.$t('calls::call.voip_activated'))
    },

    /**
     * Get the duration of the current call
     *
     * @return {Void}
     */
    updateDuration() {
      let endCallDate = this.callEndedDate || DateTime.now()
      let duration = endCallDate.diff(this.callStartedDate)

      let minutes = duration.as('minutes')
      let seconds = duration.as('seconds') % 60

      this.duration =
        (Math.floor(minutes) < 10
          ? '0' + Math.floor(minutes)
          : Math.floor(minutes)) +
        ':' +
        (Math.floor(seconds) < 10
          ? '0' + Math.floor(seconds)
          : Math.floor(seconds))
    },

    /**
     * Prepare the call component
     *
     * @return {Void}
     */
    prepareComponent(device) {
      try {
        // eslint-disable-next-line no-unused-vars
        device.on('Registering', device => {
          // console.log('Registering')
        })

        device.on('Registered', device => {
          // console.log('Registered')
          device.instance.audio.on('deviceChange', this.updateAllDevices)
        })

        // eslint-disable-next-line no-unused-vars
        device.on('Error', ({ error, Call }) => (this.error = error.message))

        this.setDevice(device)

        this.$voip.onCall(({ Call, isIncoming }) => {
          this.callEndedDate = null
          this.callStartedDate = null
          this.duration = null
          this.$options.call = Call
          this.isIncoming = isIncoming
          this.visible = true

          if (isIncoming) {
            this.lastConnectedNumber = Call.instance.parameters.From
          } else {
            this.lastConnectedNumber = Call.instance.customParameters.get('To')
          }

          // eslint-disable-next-line no-unused-vars
          Call.on('Mute', ({ isMuted, Call }) => (this.isMuted = isMuted))

          Call.on('Error', error => {
            this.visible = true
            this.call = null
            this.isIncoming = false
            this.error = error.message
          })

          Call.on('Accept', Call => {
            // console.log('Accept')
            this.isCallInProgress = true
            this.callStartedDate = DateTime.now()

            this.bindVolumeIndicators(Call.instance)
            this.durationInterval = setInterval(this.updateDuration, 1000)
          })

          Call.on('Cancel', () => {
            // console.log('Cancel')
            this.isCallInProgress = false
            this.visibility = true
            this.isIncoming = false
            this.$options.call = null
          })

          Call.on('Reject', () => {
            // console.log('Reject')
            this.duration = null
            this.callStartedDate = null
            this.isIncoming = false
            this.isCallInProgress = false
            this.$options.call = null
          })

          // eslint-disable-next-line no-unused-vars
          Call.on('Disconnect', Call => {
            // console.log('Disconnect')
            // When disconnected, set visibility to true so the user can see
            // the call data, then he can decide whether to close the call handler bar or not
            this.visible = true
            this.isCallInProgress = false
            this.$options.call = null
            this.isMuted = false
            this.isIncoming = false
            this.callEndedDate = DateTime.now()
            clearInterval(this.durationInterval)
          })
        })
      } catch (error) {
        // Catch not supported error and any other critical errors
        // twilio.js wasn't able to find WebRTC browser support. This is most likely because this page is served over http rather than https, which does not support WebRTC in many browsers. Please load this page over https and try again.
        this.error = error.message
        this.visible = true
      }
    },

    /**
     * Set the VoIP device
     */
    setDevice(device) {
      this.$options.device = device
    },

    /**
     * Get the VoIP device
     */
    getDevice() {
      return this.$options.device
    },

    /**
     * Boot the VoIP device (on user gesture)
     *
     * @return {Void}
     */
    bootVoIPDevice() {
      if (this.getDevice()) {
        document.documentElement.removeEventListener(
          'mousedown',
          this.bootVoIPDevice
        )

        return
      }

      this.$voip.ready(this.prepareComponent)
      this.$voip.ready(this.getMediaDevices)
      this.$voip.ready(device => device.instance.register())
      this.$voip.connect()
    },

    /**
     * Update all devices
     *
     * @return {Void}
     */
    updateAllDevices() {
      this.updateDevices(
        this.getDevice().instance.audio.speakerDevices.get(),
        'speaker'
      )

      this.updateDevices(
        this.getDevice().instance.audio.ringtoneDevices.get(),
        'ringtone'
      )
    },

    /**
     * Update the available audio devices
     *
     * @param  {Array} selected Selected devices
     * @param  {String} type
     *
     * @return {Void}
     */
    updateDevices(selected, type) {
      this.audioDevices[type].items = []
      this.audioDevices[type].selected = null

      let available = this.getDevice().instance.audio.availableOutputDevices

      available.forEach((device, id) => {
        let isActive = selected.size === 0 && id === 'default'

        selected.forEach(function (device) {
          if (device.deviceId === id) {
            isActive = true
          }
        })

        let item = {
          label: device.label,
          id: id,
        }

        this.audioDevices[type].items.push(item)

        if (isActive) {
          this.audioDevices[type].selected = item
        }
      })
    },

    /**
     * Get the available audio devices from navigator
     *
     * @return {Array}
     */
    getMediaDevices() {
      // https://stackoverflow.com/questions/52479734/domexception-requested-device-not-found-getusermedia
      navigator.mediaDevices
        .getUserMedia({
          audio: true,
        })
        .then(this.updateAllDevices)
        .catch(error => (this.error = error))
    },

    /**
     * Bind the volume indicators
     *
     * @param  {Object} Twilio.Call
     *
     * @return {Void}
     */
    bindVolumeIndicators(TwilioCall) {
      TwilioCall.on('volume', (inputVolume, outputVolume) => {
        let inputColor = 'red'

        if (inputVolume < 0.5) {
          inputColor = 'green'
        } else if (inputVolume < 0.75) {
          inputColor = 'yellow'
        }

        this.$refs.inputVolumeBar.style.width =
          Math.floor(inputVolume * 300) + 'px'
        this.$refs.inputVolumeBar.style.background = inputColor

        let outputColor = 'red'

        if (outputVolume < 0.5) {
          outputColor = 'green'
        } else if (outputVolume < 0.75) {
          outputColor = 'yellow'
        }

        this.$refs.outputVolumeBar.style.width =
          Math.floor(outputVolume * 300) + 'px'
        this.$refs.outputVolumeBar.style.background = outputColor
      })
    },
  },
}
</script>
