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
import { translate } from '@/Core/i18n'

import LoggedCallsCard from './components/LoggedCallsCard.vue'
import CallsTab from './components/RecordTabCall.vue'
import CallsTabPanel from './components/RecordTabCallPanel.vue'
import RecordTabTimelineCall from './components/RecordTabTimelineCall.vue'
import SettingsCalls from './components/SettingsCalls.vue'
import SettingsTwilio from './components/SettingsTwilio.vue'
import DetailPhoneCallableField from './fields/Detail/PhoneCallableField.vue'
import IndexPhoneCallableField from './fields/Index/PhoneCallableField.vue'
import CallsIndex from './views/CallsIndex.vue'
import VoIP from './VoIP'

if (window.Innoclapps) {
  Innoclapps.booting(function (app, router) {
    app.component('CallsTab', CallsTab)
    app.component('CallsTabPanel', CallsTabPanel)
    app.component('RecordTabTimelineCall', RecordTabTimelineCall)
    app.component('LoggedCallsCard', LoggedCallsCard)

    // Fields
    app.component('DetailPhoneCallableField', DetailPhoneCallableField)
    app.component('IndexPhoneCallableField', IndexPhoneCallableField)

    router.addRoute({
      path: '/calls',
      name: 'call-index',
      component: CallsIndex,
      meta: {
        title: translate('calls::call.calls'),
      },
    })

    router.addRoute('settings', {
      path: 'integrations/twilio',
      component: SettingsTwilio,
      name: 'settings-integrations-twilio',
      meta: {
        title: translate('calls::twilio.twilio'),
      },
    })

    router.addRoute('settings', {
      path: 'calls',
      name: 'calls-settings',
      component: SettingsCalls,
      meta: {
        title: translate('calls::call.calls'),
        superAdmin: true,
      },
    })

    const voipConfig = this.scriptConfig('voip') || {}

    // Voip
    if (
      voipConfig.client &&
      this.scriptConfig('user_id') &&
      app.config.globalProperties.$gate.userCan('use voip') &&
      ['twilio'].includes(voipConfig.client)
    ) {
      const VoIPInstance = new VoIP(voipConfig.client)
      app.config.globalProperties.$voip = VoIPInstance
      app.component('CallComponent', VoIPInstance.callComponent)
    }
  })
}
