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
import IButtonComponent from './IButton.vue'
import IButtonCopyComponent from './IButtonCopy.vue'
import IButtonLinkComponent from './IButtonLink.vue'

// Components
export const IButton = IButtonComponent
export const IButtonLink = IButtonLinkComponent
export const IButtonCopy = IButtonCopyComponent

// Plugin
export const IButtonPlugin = {
  install(app) {
    app.component('IButton', IButtonComponent)
    app.component('IButtonLink', IButtonLinkComponent)
    app.component('IButtonCopy', IButtonCopyComponent)
  },
}
