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
import { DateTime, Duration } from 'luxon'

const generateTimeSlots = (
  desiredStartTime,
  interval,
  period,
  maxHour = null
) => {
  // Calculate the total number of periods in a day
  const oneDay = Duration.fromObject({ days: 1 })
  const periodDuration = Duration.fromObject({ [period]: interval })
  const periodsInADay = oneDay.as(period) / interval

  const slots = []
  let startTime = DateTime.fromFormat(desiredStartTime, 'hh:mm')

  if (maxHour) {
    maxHour = DateTime.fromFormat(maxHour + ':00', 'HH:mm')
  }

  for (let i = 0; i < periodsInADay; i++) {
    if (i !== 0) {
      startTime = startTime.plus(periodDuration)
    }

    if (!maxHour || (maxHour && startTime <= maxHour.startOf('hour'))) {
      slots.push(
        startTime.toISOTime({
          suppressSeconds: true,
          suppressMilliseconds: true,
          includeOffset: false,
        })
      )
    }
  }

  return slots
}

export default generateTimeSlots
