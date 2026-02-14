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
import IVerticalNavigationComponent from './IVerticalNavigation.vue'
import IVerticalNavigationCollapsibleComponent from './IVerticalNavigationCollapsible.vue'
import IVerticalNavigationItemComponent from './IVerticalNavigationItem.vue'

// Components
export const IVerticalNavigation = IVerticalNavigationComponent
export const IVerticalNavigationCollapsible =
  IVerticalNavigationCollapsibleComponent
export const IVerticalNavigationItem = IVerticalNavigationItemComponent

// Plugin
export const IVerticalNavigationPlugin = {
  install(app) {
    app.component('IVerticalNavigation', IVerticalNavigationComponent)

    app.component(
      'IVerticalNavigationCollapsible',
      IVerticalNavigationCollapsibleComponent
    )
    app.component('IVerticalNavigationItem', IVerticalNavigationItemComponent)
  },
}
