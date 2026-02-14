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
import SettingsRecaptcha from '@/Core/views/Settings/Security/SettingsRecaptcha.vue'
import SettingsSecurity from '@/Core/views/Settings/Security/SettingsSecurity.vue'
// Settings general routes
import SettingsIndex from '@/Core/views/Settings/Settings.vue'
import SettingsFields from '@/Core/views/Settings/SettingsFields.vue'
import SettingsGeneral from '@/Core/views/Settings/SettingsGeneral.vue'
import SettingsMailableTemplates from '@/Core/views/Settings/SettingsMailableTemplates.vue'
import SettingsModules from '@/Core/views/Settings/SettingsModules.vue'
import SettingsSystemInfo from '@/Core/views/Settings/System/SettingsSystemInfo.vue'
import SettingsSystemLogs from '@/Core/views/Settings/System/SettingsSystemLogs.vue'
import SettingsTools from '@/Core/views/Settings/System/SettingsTools.vue'
import SettingsWorkflows from '@/Core/views/Workflows/WorkflowsList.vue'

import DashboardsEdit from '../views/Dashboard/DashboardsEdit.vue'
import DashboardsIndex from '../views/Dashboard/DashboardsIndex.vue'
// General routes
import Error403 from '../views/Error403.vue'
import Error404 from '../views/Error404.vue'
import NotificationsIndex from '../views/Notifications/NotificationsIndex.vue'
import OAuthAccounts from '../views/OAuth/OAuthAccounts.vue'
import ResourceImport from '../views/Resources/ImportResource.vue'
import TrashedResourceRecords from '../views/Resources/TrashedResourceRecords.vue'
import SettingsGoogle from '../views/Settings/Integrations/SettingsGoogle.vue'
// Settings integration routes
import SettingsMicrosoft from '../views/Settings/Integrations/SettingsMicrosoft.vue'
import SettingsPusher from '../views/Settings/Integrations/SettingsPusher.vue'
import SettingsZapier from '../views/Settings/Integrations/SettingsZapier.vue'

const routes = [
  {
    alias: '/',
    path: '/dashboard',
    component: DashboardsIndex,
    meta: {
      title: translate('core::dashboard.insights'),
      scrollToTop: false,
    },
  },
  {
    path: '/',
    name: 'dashboard',
    component: DashboardsIndex,
    meta: {
      title: translate('core::dashboard.insights'),
      scrollToTop: false,
    },
  },
  {
    path: '/dashboard/:id/edit',
    name: 'edit-dashboard',
    component: DashboardsEdit,
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: NotificationsIndex,
    meta: {
      title: translate('core::notifications.your'),
    },
  },
  {
    path: '/import/:resourceName',
    name: 'import-resource',
    meta: {
      title: translate('core::import.import_records'),
    },
    component: ResourceImport,
    props: route => ({ resourceName: route.params.resourceName }),
  },
  {
    path: '/oauth/accounts',
    name: 'oauth-accounts',
    component: OAuthAccounts,
    meta: {
      title: translate('core::oauth.connected_accounts'),
    },
  },
  {
    path: '/trashed/:resourceName',
    name: 'trashed-resource-records',
    component: TrashedResourceRecords,
    meta: {
      title: translate('core::app.soft_deletes.trashed_records'),
    },
    props: route => ({ resourceName: route.params.resourceName }),
  },
  {
    name: '404',
    path: '/404',
    meta: {
      title: '404',
    },
    component: Error404,
  },
  {
    name: '403',
    path: '/403',
    meta: {
      title: '403',
    },
    component: Error403,
  },
  {
    name: 'not-found',
    path: '/:pathMatch(.*)*',
    component: Error404,
  },

  // Settings routes
  {
    path: '/settings',
    name: 'settings',
    component: SettingsIndex,
    meta: {
      title: translate('core::settings.settings'),
    },
    children: [
      {
        path: 'general',
        component: SettingsGeneral,
        name: 'settings-general',
        meta: { title: translate('core::settings.general_settings') },
        alias: '/settings',
      },
      {
        path: 'fields/:resourceName',
        name: 'resource-fields',
        component: SettingsFields,
      },
      // Integration routes
      {
        path: 'integrations/microsoft',
        component: SettingsMicrosoft,
        name: 'settings-integrations-microsoft',
        meta: {
          title: translate('core::integration.microsoft'),
        },
      },
      {
        path: 'integrations/google',
        component: SettingsGoogle,
        name: 'settings-integrations-google',
        meta: {
          title: translate('core::integration.google'),
        },
      },
      {
        path: 'integrations/pusher',
        component: SettingsPusher,
        name: 'settings-integrations-pusher',
        meta: {
          title: translate('core::integration.pusher'),
        },
      },
      {
        path: 'integrations/zapier',
        component: SettingsZapier,
        name: 'settings-integrations-zapier',
        meta: {
          title: translate('core::integration.zapier'),
        },
      },
      {
        path: 'workflows',
        component: SettingsWorkflows,
        meta: { title: translate('core::workflow.workflows') },
      },
      {
        path: 'mailable-templates',
        component: SettingsMailableTemplates,
        meta: { title: translate('core::mail_template.mail_templates') },
      },
      {
        path: 'modules',
        component: SettingsModules,
        meta: { title: translate('core::modules.modules') },
      },
      {
        path: 'tools',
        component: SettingsTools,
        meta: { title: translate('core::settings.tools.tools') },
      },
      {
        path: 'info',
        component: SettingsSystemInfo,
        meta: { title: translate('core::app.system_info') },
      },
      {
        path: 'logs',
        component: SettingsSystemLogs,
        meta: { title: translate('core::app.logs') },
      },
      {
        path: 'security',
        component: SettingsSecurity,
        meta: { title: translate('core::settings.security.security') },
      },
      {
        path: 'recaptcha',
        component: SettingsRecaptcha,
        meta: { title: translate('core::settings.recaptcha.recaptcha') },
      },
    ],
  },
]

export default routes
