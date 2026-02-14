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

import ProductsCreate from './views/ProductsCreate.vue'
import ProductsEdit from './views/ProductsEdit.vue'
import ProductsIndex from './views/ProductsIndex.vue'

export default [
  {
    path: '/products',
    name: 'product-index',
    component: ProductsIndex,
    meta: {
      title: translate('billable::product.products'),
    },
    children: [
      {
        path: 'create',
        name: 'create-product',
        component: ProductsCreate,
        meta: { title: translate('billable::product.create') },
      },
      {
        path: ':id',
        name: 'view-product',
        component: ProductsEdit,
      },
      {
        path: ':id/edit',
        name: 'edit-product',
        component: ProductsEdit,
      },
    ],
  },
]
