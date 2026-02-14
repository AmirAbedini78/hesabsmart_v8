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

import CompaniesCreate from './views/CompaniesCreate.vue'
import CompaniesIndex from './views/CompaniesIndex.vue'
import CompaniesView from './views/CompaniesView.vue'
import ContactsCreate from './views/ContactsCreate.vue'
import ContactsIndex from './views/ContactsIndex.vue'
import ContactsView from './views/ContactsView.vue'

export default [
  {
    path: '/companies',
    name: 'company-index',
    component: CompaniesIndex,
    meta: {
      title: translate('contacts::company.companies'),
    },
    children: [
      {
        path: 'create',
        name: 'create-company',
        components: {
          create: CompaniesCreate,
        },
        meta: { title: translate('contacts::company.create') },
      },
    ],
  },
  {
    path: '/companies/:id',
    name: 'view-company',
    component: CompaniesView,
  },
  // contact routes
  {
    path: '/contacts',
    name: 'contact-index',
    component: ContactsIndex,
    meta: {
      title: translate('contacts::contact.contacts'),
    },
    children: [
      {
        path: 'create',
        name: 'create-contact',
        components: {
          create: ContactsCreate,
        },
        meta: { title: translate('contacts::contact.create') },
      },
    ],
  },
  {
    path: '/contacts/:id',
    name: 'view-contact',
    component: ContactsView,
  },
]
