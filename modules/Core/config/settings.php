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

use Modules\Core\Models\Setting;
use Modules\Core\Settings\Stores;

return [
    /* -----------------------------------------------------------------
     |  Default drivers
     | -----------------------------------------------------------------
     | Supported: 'array', 'json', 'database'
     |
     | When using the "override" feature, the database driver should not be used
     | as it's interacting with the database in the service provider, it works good, but it's not recommended
     */

    'default' => ENV('SETTINGS_DRIVER', 'json'),

    /* -----------------------------------------------------------------
     |  Drivers
     | -----------------------------------------------------------------
     */

    'drivers' => [

        'array' => [
            'driver' => Stores\ArrayStore::class,
        ],

        'json' => [
            'driver' => Stores\JsonStore::class,

            'options' => [
                'path' => storage_path('settings.json'),
            ],
        ],

        'database' => [
            'driver' => Stores\DatabaseStore::class,

            'options' => [
                'table' => 'settings',
                'model' => Setting::class,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Override application config values
    |--------------------------------------------------------------------------
    |
    | If defined, settings package will override these config values.
    |
    | Sample:
    |   "app.locale" => "settings.locale",
    |
    */
    'override' => [
        'app.name' => 'company_name',

        'core.logo.light' => 'logo_light',
        'core.logo.dark' => 'logo_dark',

        'core.date_format' => 'date_format',
        'core.time_format' => 'time_format',
        'core.currency' => 'currency',

        'integrations.microsoft.client_id' => 'msgraph_client_id',
        'integrations.microsoft.client_secret' => 'msgraph_client_secret',

        'integrations.google.client_id' => 'google_client_id',
        'integrations.google.client_secret' => 'google_client_secret',

        'core.recaptcha.site_key' => 'recaptcha_site_key',
        'core.recaptcha.secret_key' => 'recaptcha_secret_key',
        'core.recaptcha.ignored_ips' => 'recaptcha_ignored_ips',

        'broadcasting.connections.pusher.key' => 'pusher_app_key',
        'broadcasting.connections.pusher.secret' => 'pusher_app_secret',
        'broadcasting.connections.pusher.app_id' => 'pusher_app_id',
        'broadcasting.connections.pusher.options.cluster' => 'pusher_app_cluster',
    ],

    /*
    |--------------------------------------------------------------------------
    | Encrypted settings keys
    |--------------------------------------------------------------------------
    |
    | Define settings keys which value should be encrypted in the store
    |
    */
    'encrypted' => [
        'msgraph_client_secret',
        'google_client_secret',
        'twilio_auth_token',
    ],
];
