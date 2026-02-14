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
import { computed, onUnmounted, readonly, ref } from 'vue'
import { useStore } from 'vuex'

import { getLocale } from '../utils'

import { useFlushTableSettings } from './useTable'

export function useApp() {
  const store = useStore()
  const flushTableSettings = useFlushTableSettings()

  /**
   * @type {import('vue').Ref<boolean>}
   */
  const isDarkMode = ref(document.documentElement.classList.contains('dark'))

  // Set up a MutationObserver to watch for changes in the class attribute of the :root element
  let observer = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
      if (
        mutation.type === 'attributes' &&
        mutation.attributeName === 'class'
      ) {
        isDarkMode.value = document.documentElement.classList.contains('dark')
      }
    })
  })

  // Start observing the :root element for attribute changes
  observer.observe(document.documentElement, {
    attributes: true,
  })

  onUnmounted(() => {
    observer.disconnect()
    observer = null
  })

  /**
   * @type {import('vue').ComputedRef<Array<Object>>}
   */
  const sidebarItems = computed(() => store.state.sidebarMenuItems)

  /**
   * @type {import('vue').ComputedRef<Boolean>}
   */
  const sidebarOpen = computed({
    get() {
      return store.state.sidebarOpen
    },
    set(newValue) {
      store.commit('SET_SIDEBAR_OPEN', newValue)
    },
  })

  /**
   * @type {import('vue').ComputedRef<Array<string>>}
   */
  const locales = computed(() => scriptConfig('locales'))

  /**
   * @returns {string}
   */
  const appUrl = scriptConfig('url')

  /**
   * @type {import('vue').ComputedRef<string>}
   */
  const locale = computed(() => getLocale())

  /**
   * @type {import('vue').ComputedRef<string>}
   */
  const environment = scriptConfig('environment')

  /**
   * @type {import('vue').ComputedRef<{
   *   id: number,
   *   name: string,
   *   email: string,
   *   timezone: string,
   *   locale: string,
   *   avatar?: string,
   *   uploaded_avatar_url?: string,
   *   avatar_url?: string,
   *   mail_signature?: string,
   *   teams: Array,
   *   super_admin: boolean,
   *   access_api: boolean,
   *   time_format: string,
   *   date_format: string,
   *   dashboards?: Array<{id: number, name: string, is_default: boolean, cards: array, user_id: number}>,
   *   notifications?: {unread_count?: number, settings?: array},
   *   permissions?: Array<string>,
   *   roles?: Array<{id: number, name: string, permissions: array}>
   * }>}
   */
  const currentUser = computed(() => store.getters['users/current'])

  /**
   * @type {import('vue').Ref<Array<{
   *   id: number,
   *   name: string,
   *   email: string,
   *   timezone: string,
   *   locale: string,
   *   avatar?: string,
   *   uploaded_avatar_url?: string,
   *   avatar_url?: string,
   *   mail_signature?: string,
   *   teams: Array,
   *   super_admin: boolean,
   *   access_api: boolean,
   *   time_format: string,
   *   date_format: string,
   *   dashboards?: Array<{id: number, name: string, is_default: boolean, cards: array, user_id: number}>,
   *   notifications?: {unread_count?: number, settings?: array},
   *   permissions?: Array<string>,
   *   roles?: Array<{id: number, name: string, permissions: array}>
   * }>>}
   */
  const users = computed(() => store.state.users.collection)

  /**
   * @param {number|string} id
   * @returns {{
   *   id: number,
   *   name: string,
   *   email: string,
   *   timezone: string,
   *   locale: string,
   *   avatar?: string,
   *   uploaded_avatar_url?: string,
   *   avatar_url?: string,
   *   mail_signature?: string,
   *   teams: Array,
   *   super_admin: boolean,
   *   access_api: boolean,
   *   time_format: string,
   *   date_format: string,
   *   dashboards?: Array<{id: number, name: string, is_default: boolean, cards: array, user_id: number}>,
   *   notifications?: {unread_count?: number, settings?: array},
   *   permissions?: Array<string>,
   *   roles?: Array<{id: number, name: string, permissions: array}>
   * }|null}
   */
  function findUserById(id) {
    return store.getters['users/getById'](id)
  }

  /**
   * Logout the logged in user.
   */
  function logout() {
    let baseUrl = Innoclapps.scriptConfig('url')

    Innoclapps.request({
      baseURL: baseUrl,
      method: 'POST',
      url: '/logout',
    }).then(() => {
      window.location.href = baseUrl + '/login'
    })
  }

  /**
   * Reset any common store state.
   * @returns {void}
   */
  function resetStoreState() {
    flushTableSettings()
    store.commit('fields/RESET')
  }

  /**
   * Get or set script config.
   * @returns {any}
   */
  function scriptConfig(...args) {
    return Innoclapps.scriptConfig(...args)
  }

  /**
   * Check whether the configured mailer actually sends emails.
   * @returns {boolean}
   */
  function isMailerConfiguredToSendEmails() {
    return (
      scriptConfig('mailable_templates.can_send_via_mail_client') ||
      scriptConfig('mailable_templates.can_send_via_mailer')
    )
  }

  /**
   * Checks whether a Microsoft application is configured.
   * @returns {boolean}
   */
  function isMicrosoftGraphConfigured() {
    return Boolean(scriptConfig('integrations.microsoft.client_id'))
  }

  /**
   * Checks whether a Google project is configured.
   * @returns {boolean}
   */
  function isGoogleApiConfigured() {
    return Boolean(scriptConfig('integrations.google.client_id'))
  }

  return {
    environment,
    appUrl,
    locale,
    locales,
    currentUser,
    users,

    sidebarOpen,
    sidebarItems,

    findUserById,
    logout,
    isDarkMode: readonly(isDarkMode),
    resetStoreState,

    isGoogleApiConfigured,
    isMicrosoftGraphConfigured,
    isMailerConfiguredToSendEmails,

    scriptConfig,
  }
}
