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

namespace Modules\Core\Common\Synchronization;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Core\Models\Synchronization;

/** @mixin \Modules\Core\Models\Model */
trait Synchronizable
{
    /**
     * Get the synchronizable synchronizer class
     *
     * @return \Modules\Core\Contracts\Synchronization\Synchronizable
     */
    abstract public function synchronizer();

    /**
     * Boot the Synchronizable trait
     */
    protected static function bootSynchronizable(): void
    {
        // Start a new synchronization once created.
        static::created(function ($synchronizable) {
            $synchronizable->synchronization()->create();
        });

        // Stop and delete associated synchronization.
        static::deleting(function ($synchronizable) {
            $synchronizable->synchronization->delete();
        });
    }

    /**
     * Get the model synchronization model
     */
    public function synchronization(): MorphOne
    {
        return $this->morphOne(Synchronization::class, 'synchronizable');
    }
}
