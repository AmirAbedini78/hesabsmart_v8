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
import { ref, toValue } from 'vue'
import { useRouter } from 'vue-router'
import FileDownload from 'js-file-download'

import { emitGlobal } from '@/Core/composables/useGlobalEventListener'
import { throwConfetti } from '@/Core/utils'

import { useFloatingResourceModal } from './useFloatingResourceModal'
import { useForm } from './useForm'

export function useAction(resourceName, selectedIds, config = {}) {
  const selectedAction = ref(null)
  const actionBeingExecuted = ref(false)
  const confirmationModalVisible = ref(false)

  const { floatResource } = useFloatingResourceModal()
  const router = useRouter()

  const { form } = useForm(
    { ids: [] },
    {
      onSuccess: response => onActionExecuted(response, config.callback),
    }
  )

  function handleSelectionChange(action) {
    selectedAction.value = action

    if (action.floatResource) {
      floatResource({
        resourceName,
        resourceId: toValue(selectedIds)[0],
        mode: action.floatResource,
      })
    } else if (action.withoutConfirmation) {
      executeAction()
    } else {
      showConfirmationModal()
    }
  }

  async function executeAction(hooks = {}) {
    actionBeingExecuted.value = true

    try {
      if (hooks.onSubmit) hooks.onSubmit(form)

      await form
        .fill('ids', toValue(selectedIds))
        .post(`${resourceName}/actions/${selectedAction.value.uriKey}/run`, {
          params: config.additionalRequestParams || {},
          responseType: selectedAction.value.responseType,
        })
      if (hooks.onSubmit) hooks.onSubmitted(form)
    } catch (err) {
      if (hooks.onError) hooks.onError(err)
    } finally {
      actionBeingExecuted.value = false
    }
  }

  function handleActionResponse(response) {
    let data = response.data
    let headers = response.headers

    if (data instanceof Blob) {
      FileDownload(
        data,
        headers['content-disposition'].split('filename=')[1] || 'unknown'
      )
    } else if (data.error) {
      Innoclapps.error(data.error)
    } else if (data.success) {
      Innoclapps.success(data.success)
    } else if (data.info) {
      Innoclapps.info(data.info)
    } else if (data.confetti) {
      throwConfetti()
    } else if (data.navigateTo) {
      router.push(data.navigateTo)
    }
  }

  function onActionExecuted(response, callback) {
    let data = response.data

    if (data.openInNewTab) {
      window.open(data.openInNewTab, '_blank')
    } else {
      handleActionResponse(response)

      let params = Object.assign({}, selectedAction.value, {
        ids: selectedIds.value,
        response: data,
        resourceName: resourceName,
      })

      emitGlobal('action-executed', params)

      if (callback) {
        callback(params)
      }
    }

    selectedAction.value = null
    hideConfirmationModal()
  }

  function handleConfirmationModalHidden() {
    selectedAction.value = null
  }

  function showConfirmationModal() {
    confirmationModalVisible.value = true
  }

  function hideConfirmationModal() {
    confirmationModalVisible.value = false
  }

  return {
    form,
    handleSelectionChange,
    selectedAction,
    executeAction,
    actionBeingExecuted,
    confirmationModalVisible,
    handleConfirmationModalHidden,
    showConfirmationModal,
    hideConfirmationModal,
  }
}
