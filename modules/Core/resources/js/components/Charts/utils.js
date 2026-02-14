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
import map from 'lodash/map'

export function resultToChartData(result) {
  return {
    labels: map(result, 'label'),
    series: [
      map(result, data => {
        return {
          meta: data.label,
          value: data.value,
          color: data.color,
        }
      }),
    ],
  }
}

export function hasData(data) {
  const totalSeries = data.series.length

  if (totalSeries === 0) {
    return false
  }

  let anySerieHasData = false

  for (let i = 0; i < totalSeries; i++) {
    if (data.series[i].length > 0) {
      anySerieHasData = data.series[i].some(val => val.value > 0)

      if (anySerieHasData) {
        break
      }
    }
  }

  return anySerieHasData
}
