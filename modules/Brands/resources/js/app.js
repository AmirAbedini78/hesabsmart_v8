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

import SettingsBrands from './components/SettingsBrands.vue'
import BrandsCreate from './views/BrandsCreate.vue'
import BrandsEdit from './views/BrandsEdit.vue'

if (window.Innoclapps) {
  Innoclapps.booting(function (app, router) {
    router.addRoute('settings', {
      path: '/settings/brands',
      component: SettingsBrands,
      name: 'index-brand',
      meta: {
        title: translate('brands::brand.brands'),
        superAdmin: true,
      },
    })

    router.addRoute('settings', {
      path: '/settings/brands/create',
      component: BrandsCreate,
      name: 'create-brand',
      meta: { superAdmin: true },
    })

    router.addRoute('settings', {
      path: '/settings/brands/:id/edit',
      component: BrandsEdit,
      name: 'edit-brand',
      meta: { superAdmin: true },
    })
  })
}
