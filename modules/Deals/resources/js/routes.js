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
import { useStorage } from '@vueuse/core'

import { translate } from '@/Core/i18n'

import DealsBoard from './views/DealsBoard.vue'
import DealsCreate from './views/DealsCreate.vue'
import DealsImport from './views/DealsImport.vue'
import DealsIndex from './views/DealsIndex.vue'
import DealsView from './views/DealsView.vue'

const isBoardDefaultView = useStorage('deals-board-view-default', false)

export default [
  {
    path: '/deals',
    name: 'deal-index',
    component: DealsIndex,
    meta: {
      title: translate('deals::deal.deals'),
      subRoutes: ['create-deal'],
      boardRoute: 'deal-board',
    },
    beforeEnter: async (to, from) => {
      // Check if the deals board is active
      if (
        isBoardDefaultView.value &&
        from.name != to.meta.boardRoute &&
        to.meta.subRoutes.indexOf(to.name) === -1
      ) {
        return { name: to.meta.boardRoute, query: to.query }
      }

      if (to.meta.subRoutes.indexOf(to.name) === -1) {
        isBoardDefaultView.value = false
      }
    },
    children: [
      {
        path: 'create',
        name: 'create-deal',
        components: {
          create: DealsCreate,
        },
        meta: { title: translate('deals::deal.create') },
      },
    ],
  },
  {
    path: '/import/deals',
    name: 'import-deal',
    component: DealsImport,
    meta: { title: translate('deals::deal.import') },
  },
  {
    path: '/deals/board',
    name: 'deal-board',
    component: DealsBoard,
    meta: {
      title: translate('deals::deal.deals'),
    },
    beforeEnter: () => {
      isBoardDefaultView.value = true
    },
  },
  {
    path: '/deals/:id',
    name: 'view-deal',
    component: DealsView,
  },
]
