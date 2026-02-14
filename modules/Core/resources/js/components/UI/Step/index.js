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
import IStepCircleComponent from './IStepCircle.vue'
import IStepsCircleComponent from './IStepsCircle.vue'

// Components
export const IStepCircle = IStepCircleComponent
export const IStepsCircle = IStepsCircleComponent

// Plugin
export const IStepsPlugin = {
  install(app) {
    app.component('IStepCircle', IStepCircleComponent)
    app.component('IStepsCircle', IStepsCircleComponent)
  },
}
