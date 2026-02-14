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

use Illuminate\Support\Facades\Broadcast;
use Modules\Deals\Models\Deal;
use Modules\Users\Models\User;

Broadcast::channel('Modules.Deals.Models.Deal.{dealId}', function (User $user, string $dealId) {
    return $user->can('view', Deal::findOrFail($dealId));
});
