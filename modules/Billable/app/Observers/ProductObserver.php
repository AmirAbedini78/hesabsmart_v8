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

namespace Modules\Billable\Observers;

use Modules\Billable\Models\BillableProduct;
use Modules\Billable\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "deleting" event.
     */
    public function deleting(Product $product): void
    {
        if ($product->isForceDeleting()) {
            $product->loadMissing('billables')->billables->each(function (BillableProduct $product) {
                $product->delete();
            });
        }
    }
}
