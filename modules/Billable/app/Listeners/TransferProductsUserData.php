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

namespace Modules\Billable\Listeners;

use Modules\Billable\Models\Product;
use Modules\Users\Events\TransferringUserData;

class TransferProductsUserData
{
    /**
     * Handle the event.
     */
    public function handle(TransferringUserData $event): void
    {
        Product::withTrashed()->where('created_by', $event->fromUserId)->update(['created_by' => $event->toUserId]);
    }
}
