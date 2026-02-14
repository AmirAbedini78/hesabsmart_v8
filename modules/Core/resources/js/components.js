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
import { defineAsyncComponent } from 'vue'
import FileUpload from 'vue-upload-component'
import Notifications from 'notiwind'

import { translate } from '@/Core/i18n'

import AsyncComponentLoader from './components/AsyncComponentLoader.vue'

const FullCalendar = defineAsyncComponent({
  loader: () => import('@/Core/components/FullCalendar.vue'),
  loadingComponent: AsyncComponentLoader,
})

const SortableDraggable = defineAsyncComponent({
  loader: () => import('vuedraggable'),
  loadingComponent: AsyncComponentLoader,
})

const TextCollapse = defineAsyncComponent({
  loader: () => import('./components/TextCollapse.vue'),
  loadingComponent: AsyncComponentLoader,
})

const CropsAndUploadsImage = defineAsyncComponent({
  loader: () => import('./components/CropsAndUploadsImage.vue'),
  loadingComponent: AsyncComponentLoader,
})

import ActionBulkEditModal from './components/Actions/ActionBulkEditModal.vue'
import ActionModal from './components/Actions/ActionModal.vue'
import Anchor from './components/Anchor.vue'
import CardAsyncTable from './components/Cards/CardAsyncTable.vue'
import CardTable from './components/Cards/CardTable.vue'
import PresentationChart from './components/Charts/PresentationChart.vue'
import ProgressionChart from './components/Charts/ProgressionChart.vue'
import DatePicker from './components/DatePicker/DatePicker.vue'
import DateRangePicker from './components/DatePicker/DateRangePicker.vue'
import { Editor, EditorText } from './components/Editor'
import InputSearch from './components/InputSearch.vue'
import MainLayout from './components/MainLayout.vue'
import NavbarItems from './components/NavbarItems.vue'
import NavbarSeparator from './components/NavbarSeparator.vue'
import Panel from './components/Panel.vue'
import Panels from './components/Panels.vue'
import ResourceDetailsPanel from './components/Resource/ResourceDetailsPanel.vue'
import ResourceExport from './components/Resource/ResourceExport.vue'
import ResourceMediaPanel from './components/Resource/ResourceMediaPanel.vue'
import ResourceTable from './components/Resource/Table/ResourceTable.vue'
import TheFloatingResourceModal from './components/TheFloatingResourceModal.vue'
import TheFloatNotifications from './components/TheFloatNotifications.vue'
import TheNavbar from './components/TheNavbar.vue'
import TheSidebar from './components/TheSidebar.vue'
import { IAlertPlugin } from './components/UI/Alert'
import { IBadgePlugin } from './components/UI/Badge'
import { IButtonPlugin } from './components/UI/Button'
import { ICardPlugin } from './components/UI/Card'
import ICustomSelect from './components/UI/CustomSelect'
import { IDialogPlugin } from './components/UI/Dialog'
import { IDropdownPlugin } from './components/UI/Dropdown'
import { IFormPlugin } from './components/UI/Form'
import { IFormCheckboxPlugin } from './components/UI/Form/Checkbox'
import { IFormRadioPlugin } from './components/UI/Form/Radio'
import { IFormSwitchPlugin } from './components/UI/Form/Switch'
import IActionMessage from './components/UI/IActionMessage.vue'
import IAvatar from './components/UI/IAvatar.vue'
import IColorSwatch from './components/UI/IColorSwatch.vue'
import Icon from './components/UI/Icon.vue'
import IEmptyState from './components/UI/IEmptyState.vue'
import IIconPicker from './components/UI/IIconPicker.vue'
import ILink from './components/UI/ILink.vue'
import ILinkBase from './components/UI/ILinkBase.vue'
import IOverlay from './components/UI/IOverlay.vue'
import ISpinner from './components/UI/ISpinner.vue'
import { IPopoverPlugin } from './components/UI/Popover'
import { IStepsPlugin } from './components/UI/Step'
import { ITabsPlugin } from './components/UI/Tab'
import { ITablePlugin } from './components/UI/Table'
import { ITextPlugin } from './components/UI/Text'
import { ITooltipPlugin } from './components/UI/Tooltip'
import { IVerticalNavigationPlugin } from './components/UI/VerticalNavigation'
import BaseDetailField from './fields/BaseDetailField.vue'
import BaseFormField from './fields/BaseFormField.vue'
import BaseIndexField from './fields/BaseIndexField.vue'
import BaseSelectField from './fields/BaseSelectField.vue'
import DetailFields from './fields/DetailFields.vue'
import FieldInlineEdit from './fields/FieldInlineEdit.vue'
import FieldsButtonCollapse from './fields/FieldsButtonCollapse.vue'
import FieldsPlaceholder from './fields/FieldsPlaceholder.vue'
import FormFields from './fields/FormFields.vue'
import ActionPanel from './views/ActionPanel.vue'

export default function (app) {
  app
    .use(Notifications)
    .use(IButtonPlugin)
    .use(ICardPlugin)
    .use(IDropdownPlugin)
    .use(IPopoverPlugin)
    .use(ITablePlugin)
    .use(IFormPlugin)
    .use(IFormSwitchPlugin)
    .use(IFormCheckboxPlugin)
    .use(IFormRadioPlugin)
    .use(ITabsPlugin)
    .use(ITextPlugin)
    .use(IStepsPlugin)
    .use(ITooltipPlugin)
    .use(IAlertPlugin)
    .use(IBadgePlugin)
    .use(IVerticalNavigationPlugin)
    .use(IDialogPlugin, {
      dialog: {
        labels: {
          cancelText: translate('core::app.cancel'),
          okText: 'Ok',
        },
      },
      confirmation: {
        labels: {
          title: translate('core::actions.confirmation_message'),
          confirmText: translate('core::app.confirm'),
          cancelText: translate('core::app.cancel'),
        },
      },
    })

  app
    .component('FullCalendar', FullCalendar)
    .component('SortableDraggable', SortableDraggable)
    .component('FileUpload', FileUpload)

    .component('IActionMessage', IActionMessage)
    .component('IAvatar', IAvatar)
    .component('ICustomSelect', ICustomSelect)
    .component('IOverlay', IOverlay)
    .component('IEmptyState', IEmptyState)
    .component('IIconPicker', IIconPicker)
    .component('ISpinner', ISpinner)
    .component('IColorSwatch', IColorSwatch)
    .component('ILink', ILink)
    .component('ILinkBase', ILinkBase)

    .component('MainLayout', MainLayout)
    .component('TheNavbar', TheNavbar)
    .component('NavbarItems', NavbarItems)
    .component('NavbarSeparator', NavbarSeparator)
    .component('TheSidebar', TheSidebar)

    .component('TheFloatNotifications', TheFloatNotifications)
    .component('TheFloatingResourceModal', TheFloatingResourceModal)

    .component('DatePicker', DatePicker)
    .component('DateRangePicker', DateRangePicker)

    .component('ActionPanel', ActionPanel)

    .component('Icon', Icon)

    .component('Anchor', Anchor)

    .component('ActionModal', ActionModal)
    .component('ActionBulkEditModal', ActionBulkEditModal)

    .component('ProgressionChart', ProgressionChart)
    .component('PresentationChart', PresentationChart)

    .component('CardTable', CardTable)
    .component('CardAsyncTable', CardAsyncTable)

    .component('ResourceTable', ResourceTable)
    .component('ResourceExport', ResourceExport)

    .component('CropsAndUploadsImage', CropsAndUploadsImage)
    .component('TextCollapse', TextCollapse)

    .component('Editor', Editor)
    .component('EditorText', EditorText)

    .component('Panel', Panel)
    .component('Panels', Panels)

    .component('ResourceMediaPanel', ResourceMediaPanel)
    .component('ResourceDetailsPanel', ResourceDetailsPanel)

    .component('BaseFormField', BaseFormField)
    .component('BaseSelectField', BaseSelectField)
    .component('FormFields', FormFields)
    .component('DetailFields', DetailFields)
    .component('BaseDetailField', BaseDetailField)
    .component('BaseIndexField', BaseIndexField)
    .component('FieldsButtonCollapse', FieldsButtonCollapse)
    .component('FieldsPlaceholder', FieldsPlaceholder)
    .component('FieldInlineEdit', FieldInlineEdit)

    .component('InputSearch', InputSearch)
}
