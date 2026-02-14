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
import { useI18n } from 'vue-i18n'

import { useApp } from '@/Core/composables/useApp'
import { useDates } from '@/Core/composables/useDates'
import { useResourceable } from '@/Core/composables/useResourceable'

import { useActivityTypes } from './useActivityTypes'

export function useActivities() {
  const { t } = useI18n()
  const { findTypeByFlag } = useActivityTypes()
  const { scriptConfig, currentUser } = useApp()
  const { DateTime } = useDates()

  async function createFollowUpActivity(
    date,
    viaResource,
    viaResourceId,
    relatedToDisplayName,
    attributes = {}
  ) {
    let dueDateTimeInstance = DateTime.fromFormat(date, 'yyyy-MM-dd')
      .set({
        hour: scriptConfig('activities.defaults.hour'),
        minute: scriptConfig('activities.defaults.minutes'),
        second: 0,
      })
      .toUTC()

    const { createResource } = useResourceable(
      Innoclapps.resourceName('activities')
    )

    let activity = await createResource(
      Object.assign(
        {
          title: t('activities::activity.follow_up_with_title', {
            with: relatedToDisplayName,
          }),
          activity_type_id: findTypeByFlag('task').id,
          due_date: dueDateTimeInstance.toISO(),
          end_date: dueDateTimeInstance.toISODate(),
          reminder_minutes_before: scriptConfig('defaults.reminder_minutes'),
          user_id: currentUser.value.id,
          [viaResource]: [viaResourceId],
        },
        attributes
      )
    )

    return activity
  }

  return {
    createFollowUpActivity,
  }
}
