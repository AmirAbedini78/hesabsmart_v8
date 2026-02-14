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
    'name' => 'Users',
    /*
    |--------------------------------------------------------------------------
    | User invitation config
    |--------------------------------------------------------------------------
    |
    */
    'invitation' => [
        'expires_after' => env('USER_INVITATION_EXPIRES_AFTER', 3), // in days
    ],
];
