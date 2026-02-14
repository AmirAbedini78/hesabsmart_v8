<?php
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

return [
    'name' => 'Transalator',

    /**
     * The file path where the JSON generator will generate the translations
     */
    'json' => storage_path('i18n-locales.js'),

    /**
     * The translator custom path for OverrideFileLoader
     */
    'custom' => lang_path('.custom'),
];
