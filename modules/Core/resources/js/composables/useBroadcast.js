/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */
import { ref, toValue, watch, watchPostEffect } from 'vue'

export function useNotification(userId, callback) {
  const canListen = Innoclapps.broadcaster?.hasDriver() || false

  if (!canListen) {
    return
  }

  window.Echo.private('Modules.Users.Models.User.' + userId).notification(
    callback
  )
}

export function usePrivateChannel(channel, event, callback) {
  const canListen = Innoclapps.broadcaster?.hasDriver() || false

  const channelName = ref(null)

  watchPostEffect(() => {
    channelName.value = toValue(channel)
  })

  watch(
    channelName,
    (newChannel, oldChannel) => {
      // console.log('watch effect triggered ' + channelName.value)
      if (oldChannel) {
        stopListening(oldChannel)
      }

      if (newChannel) {
        listen()
      }
    },
    { immediate: true }
  )

  function listen() {
    if (!canListen || !channelName.value) return
    // console.log('listening ' + channelName.value)
    window.Echo.private(channelName.value).listen(event, callback)
  }

  function stopListening(ch) {
    if (!canListen || (!ch && !channelName.value)) return
    // console.log('stop ' + (ch || channelName.value))
    window.Echo.private(ch || channelName.value).stopListening(event, callback)
  }

  return { listen, stopListening }
}
