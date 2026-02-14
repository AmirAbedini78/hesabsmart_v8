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
// Parents selector
Element.prototype.parents = function (selector) {
  var elements = []
  var elem = this
  var ishaveselector = selector !== undefined

  while ((elem = elem.parentElement) !== null) {
    if (elem.nodeType !== Node.ELEMENT_NODE) {
      continue
    }

    if (!ishaveselector || elem.matches(selector)) {
      elements.push(elem)
    }
  }

  return elements
}
