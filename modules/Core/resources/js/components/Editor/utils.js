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
const tinyMCELocales = [
  'ar',
  'ar_SA',
  'az',
  'be',
  'bg_BG',
  'bn_BD',
  'ca',
  'cs',
  'cy',
  'da',
  'de',
  'el',
  'eo',
  'es',
  'es_MX',
  'et',
  'eu',
  'fa',
  'fi',
  'fr_FR',
  'ga',
  'gl',
  'he_IL',
  'hr',
  'hu_HU',
  'hy',
  'id',
  'is_IS',
  'it',
  'ja',
  'ka_GE',
  'kab',
  'kk',
  'ko_KR',
  'ku',
  'lt',
  'lv',
  'nb_NO',
  'ne',
  'nl',
  'nl_BE',
  'oc',
  'pl',
  'pt_BR',
  'pt_PT',
  'ro',
  'ru',
  'sk',
  'sl_SI',
  'sq',
  'sr',
  'sv_SE',
  'ta',
  'tg',
  'th_TH',
  'tr',
  'ug',
  'uk',
  'uz',
  'vi',
  'zh_CN',
  'zh_HK',
  'zh_MO',
  'zh_SG',
  'zh_TW',
]

export function mapPHPLocaleToTinyMCE(phpLocale) {
  if (tinyMCELocales.includes(phpLocale)) {
    return phpLocale
  }

  const generalLocale = phpLocale.split('_')[0]

  if (tinyMCELocales.includes(generalLocale)) {
    return generalLocale
  }

  return 'en'
}
