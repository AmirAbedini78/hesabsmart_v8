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

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Builder;

/** @mixin \Modules\Core\Models\Model */
trait HasDisplayOrder
{
    /**
     * Boot the HasDisplayOrder trait.
     */
    protected static function bootHasDisplayOrder()
    {
        static::addGlobalScope('displayOrder', fn (Builder $query) => $query->orderByDisplayOrder());
    }

    /**
     * Scope a query to order the model by "display_order" column.
     */
    public function scopeOrderByDisplayOrder(Builder $query): void
    {
        $query->orderBy('display_order');
    }
}
