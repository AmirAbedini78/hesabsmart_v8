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
import { createVNode, render } from 'vue'

export default class RenderComponent {
  constructor(options) {
    this.el = options.el
    this.rootComponent = options.rootComponent
    this.props = options?.props ?? {}
    this.appContext = { ...(options?.appContext ?? {}) }
  }

  mount() {
    const componentVNode = createVNode(this.rootComponent, this.props)
    render(componentVNode, this.el)
  }

  unmount() {
    render(null, this.el)
  }
}
