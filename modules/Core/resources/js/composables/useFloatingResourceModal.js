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
import { useRoute, useRouter } from 'vue-router'

export function useFloatingResourceModal() {
  const router = useRouter()
  const route = useRoute()

  function floatResource({ resourceName, resourceId, mode }) {
    router.push({
      query: {
        ...route.query,
        floating_resource: resourceName,
        floating_resource_id: resourceId,
        mode: mode,
      },
    })
  }

  function floatResourceInDetailMode(config) {
    floatResource({ ...config, mode: 'detail' })
  }

  function floatResourceInEditMode(config) {
    floatResource({ ...config, mode: 'edit' })
  }

  return {
    floatResource,
    floatResourceInEditMode,
    floatResourceInDetailMode,
  }
}
