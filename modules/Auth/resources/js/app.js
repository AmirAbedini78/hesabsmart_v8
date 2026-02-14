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
import AuthLogin from './components/AuthLogin.vue'
import AuthPasswordEmail from './components/AuthPasswordEmail.vue'
import AuthPasswordReset from './components/AuthPasswordReset.vue'

if (window.Innoclapps) {
  Innoclapps.booting(function (app) {
    app
      .component('AuthLogin', AuthLogin)
      .component('AuthPasswordEmail', AuthPasswordEmail)
      .component('AuthPasswordReset', AuthPasswordReset)
  })
}
