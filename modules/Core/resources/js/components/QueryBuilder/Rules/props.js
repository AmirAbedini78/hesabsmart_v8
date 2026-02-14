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
export default {
  operand: { required: true },
  isNullable: { required: true, type: Boolean },
  index: { required: true, type: Number },
  query: { type: Object, required: false },
  rule: { type: Object, required: true },
  labels: { required: true },
  operator: { required: true },
  isBetween: Boolean,
  readonly: Boolean,
}
