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

import SettingsThemeStyle from './components/SettingsThemeStyle.vue'

if (window.Innoclapps) {
  Innoclapps.booting(function (app, router) {
    router.addRoute('settings', {
      path: '/settings/theme-style',
      component: SettingsThemeStyle,
      meta: { title: translate('themestyle::style.theme_style') },
    })
  })
}
