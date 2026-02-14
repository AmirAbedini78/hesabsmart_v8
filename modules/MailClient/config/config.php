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
    'name' => 'MailClient',

    'reply_prefix' => env('MAIL_MESSAGE_REPLY_PREFIX', 'RE: '),

    'forward_prefix' => env('MAIL_MESSAGE_FORWARD_PREFIX', 'FW: '),

    /*
    |--------------------------------------------------------------------------
    | Mail client configuration
    |--------------------------------------------------------------------------
    |
    */
    'sync' => [
        /*
        |--------------------------------------------------------------------------
        | Sync mail client interval definition in cron style
        |--------------------------------------------------------------------------
        |
        | By default the mail client synchronizer, sync emails every 3 minutes, the interval can be defined below.
        */
        'interval' => env('MAIL_CLIENT_SYNC_INTERVAL', '*/3 * * * *'),
    ],
];
