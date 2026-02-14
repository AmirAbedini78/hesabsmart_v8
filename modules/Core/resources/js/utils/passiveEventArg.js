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
/**
 * Get the passive third arguemtn and check whether the browser supports it
 *
 * @returns {boolean|object}
 */
function passiveEventArg() {
  // Cache checks
  if (Object.hasOwn(window, '__passiveEvt')) {
    return window.__passiveEvt
  }

  let result = false

  try {
    const arg = Object.defineProperty({}, 'passive', {
      get() {
        result = {
          passive: true,
        }

        return true
      },
    })

    window.addEventListener('testpassive', arg, arg)
    window.remove('testpassive', arg, arg)
  } catch (e) {
    /* */
  }

  window.__passiveEvt = result

  return result
}

export default passiveEventArg
