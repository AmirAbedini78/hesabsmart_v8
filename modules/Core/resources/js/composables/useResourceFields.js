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
import { computed, isRef, ref, toValue, watch } from 'vue'
import { useStore } from 'vuex'
import cloneDeep from 'lodash/cloneDeep'
import each from 'lodash/each'

import { useApp } from './useApp'

export function useResourceFields(list = []) {
  const store = useStore()
  const { scriptConfig } = useApp()

  const fields = ref(cloneDeep(toValue(list) || []))

  let resource = null
  let resourceWatcherStopHandler = null

  const hasFields = computed(() => fields.value.length > 0)

  const totalCollapsable = computed(
    () => fields.value.filter(field => field.collapsed).length
  )

  function findField(attribute) {
    return fields.value.find(field => field.attribute === attribute)
  }

  /**
   * Set the resource object that should be used to hydrate the fields values.
   * In most cases, this should be used for resource update to populate
   * the fields values from the object and as well sync any changes.
   */
  function setResource(refObj) {
    if (!isRef(refObj)) {
      return console.error('The resource must be a ref.')
    }

    configureResourceSyncWatcher(refObj)

    resource = refObj

    hydrateFields(refObj.value)
  }

  function configureResourceSyncWatcher(refObj) {
    if (resourceWatcherStopHandler) {
      resourceWatcherStopHandler()
    }

    resourceWatcherStopHandler = watch(
      () => refObj.value._sync_timestamp,
      () => {
        hydrateFields(resource.value)
      }
    )
  }

  function updateField(attribute, data) {
    let field = findField(attribute)

    if (!field) {
      console.trace(`Cannot update "${attribute}" field. [FIELD NOT FOUND].`)

      return
    }

    each(data, (val, key) => (field[key] = val))
  }

  function updateFieldValue(attribute, value) {
    updateField(attribute, { value })
  }

  function hydrateFields(data) {
    fields.value.forEach(field => {
      let value = extractValueFromData(field, data)

      if (value !== undefined) {
        field.value = value
      }
    })
  }

  function extractValueFromData(field, data) {
    if (field.belongsToRelation) {
      return Object.hasOwn(data, field.belongsToRelation)
        ? data[field.belongsToRelation]
        : undefined
    } else {
      // Perhaps heading field, it has no attribute.
      if (field.attribute && Object.hasOwn(data, field.attribute)) {
        return data[field.attribute]
      } else {
        return undefined
      }
    }
  }

  async function getFields(resourceName, view, params = {}) {
    let clonedFields = cloneDeep(
      await store.dispatch('fields/getForResource', {
        resourceName: toValue(resourceName),
        view,
        ...params,
      })
    )

    // Some performance improvements.
    clonedFields.forEach((field, idx) => {
      if (field.component === 'timezone-field') {
        clonedFields[idx].timezones = Object.freeze(field.timezones)
      } else if (
        field.attribute === 'country_id' ||
        field.attribute === 'industry_id'
      ) {
        clonedFields[idx].options = Object.freeze(field.options)
      }
    })

    return clonedFields
  }

  async function getCreateFields(resourceName, params = {}) {
    return await getFields(
      resourceName,
      scriptConfig('fields.views.create'),
      params
    )
  }

  async function getDetailFields(resourceName, id, params = {}) {
    return await getFields(resourceName, scriptConfig('fields.views.detail'), {
      ...params,
      resourceId: id,
    })
  }

  async function getUpdateFields(resourceName, id, params = {}) {
    return await getFields(resourceName, scriptConfig('fields.views.update'), {
      ...params,
      resourceId: id,
    })
  }

  async function getIndexFields(resourceName, params = {}) {
    return await getFields(
      resourceName,
      scriptConfig('fields.views.index'),
      params
    )
  }

  return {
    fields,
    hasFields,
    hydrateFields,
    setResource,
    findField,
    updateField,
    updateFieldValue,
    totalCollapsable,

    getCreateFields,
    getUpdateFields,
    getDetailFields,
    getIndexFields,
  }
}
