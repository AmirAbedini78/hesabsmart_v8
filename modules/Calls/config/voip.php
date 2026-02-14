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
    'client' => env('VOIP_CLIENT'),
    // Route names
    'endpoints' => [
        'call' => 'voip.call',
        'events' => 'voip.events',
    ],
];
