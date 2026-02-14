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

namespace Modules\Deals\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;

/** @mixin \Modules\Core\Models\Model */
trait HasDeals
{
    /**
     * Get all of the deals that are associated with the model.
     */
    public function deals(): MorphToMany
    {
        return $this->morphToMany(\Modules\Deals\Models\Deal::class, 'dealable');
    }

    /**
     * Get all of the open deals associated with the model.
     */
    public function openDeals(): MorphToMany
    {
        return $this->deals()->open();
    }

    /**
     * Get all of the won deals associated with the model.
     */
    public function wonDeals(): MorphToMany
    {
        return $this->deals()->won();
    }

    /**
     * Get all of the lost deals associated with the model.
     */
    public function lostDeals(): MorphToMany
    {
        return $this->deals()->lost();
    }

    /**
     * Get all of the closed deals associated with the model.
     */
    public function closedDeals(): MorphToMany
    {
        return $this->deals()->closed();
    }
}
