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

import DocumentsCreate from './views/DocumentsCreate.vue'
import DocumentsEdit from './views/DocumentsEdit.vue'
import DocumentsIndex from './views/DocumentsIndex.vue'
import DocumentsTemplatesCreate from './views/DocumentsTemplatesCreate.vue'
import DocumentsTemplatesEdit from './views/DocumentsTemplatesEdit.vue'
import DocumentsTemplatesIndex from './views/DocumentsTemplatesIndex.vue'

export default [
  {
    path: '/documents',
    name: 'document-index',
    component: DocumentsIndex,
    meta: {
      title: translate('documents::document.documents'),
    },
    children: [
      {
        path: 'create',
        name: 'create-document',
        components: {
          create: DocumentsCreate,
        },
        meta: { title: translate('documents::document.create') },
      },
      {
        path: ':id',
        name: 'view-document',
        components: {
          edit: DocumentsEdit,
        },
      },
      {
        path: ':id/edit',
        name: 'edit-document',
        components: {
          edit: DocumentsEdit,
        },
      },
    ],
  },
  {
    path: '/document-templates',
    name: 'document-templates-index',
    component: DocumentsTemplatesIndex,
    meta: {
      title: translate('documents::document.template.templates'),
    },
    children: [
      {
        path: 'create',
        name: 'create-document-template',
        components: {
          create: DocumentsTemplatesCreate,
        },
        meta: {
          title: translate('documents::document.template.create'),
        },
      },
      {
        path: ':id/edit',
        name: 'edit-document-template',
        components: {
          edit: DocumentsTemplatesEdit,
        },
      },
    ],
  },
]
