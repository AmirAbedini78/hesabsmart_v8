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
import IFormErrorComponent from './IFormError.vue'
import IFormGroupComponent from './IFormGroup.vue'
import IFormInputComponent from './IFormInput.vue'
import IFormInputDropdownComponent from './IFormInputDropdown.vue'
import IFormLabelComponent from './IFormLabel.vue'
import IFormNumericInputComponent from './IFormNumericInput.vue'
import IFormSelectComponent from './IFormSelect.vue'
import IFormTextComponent from './IFormText.vue'
import IFormTextareaComponent from './IFormTextarea.vue'

// Components
export const IFormGroup = IFormGroupComponent
export const IFormLabel = IFormLabelComponent
export const IFormError = IFormErrorComponent
export const IFormText = IFormTextComponent
export const IFormInput = IFormInputComponent
export const IFormInputDropdown = IFormInputDropdownComponent
export const IFormNumericInput = IFormNumericInputComponent
export const IFormSelect = IFormSelectComponent
export const IFormTextarea = IFormTextareaComponent

// Plugin
export const IFormPlugin = {
  install(app) {
    app.component('IFormGroup', IFormGroupComponent)
    app.component('IFormLabel', IFormLabelComponent)
    app.component('IFormError', IFormErrorComponent)
    app.component('IFormText', IFormTextComponent)
    app.component('IFormInput', IFormInputComponent)
    app.component('IFormNumericInput', IFormNumericInputComponent)
    app.component('IFormSelect', IFormSelectComponent)
    app.component('IFormTextarea', IFormTextareaComponent)
    app.component('IFormInputDropdown', IFormInputDropdownComponent)
  },
}
