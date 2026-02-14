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

namespace Modules\Calls\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Calls\Models\Call;
use Modules\Core\Models\Model;

trait HasCalls
{
    /**
     * Boot the HasCalls trait
     */
    protected static function bootHasCalls(): void
    {
        static::deleting(function (Model $model) {
            if ($model->isReallyDeleting()) {
                // We will get all of the model associated calls and then will check
                // if the call belonging to the model is only associated with one resource (the one being deleted)
                // when it's associated only with one resource, we will delete the call as it's the last call.
                $calls = $model->calls()->withCountAssociations()->get();

                foreach ($calls as $call) {
                    $totalAssociations = 0;

                    foreach ($call->resource()->associateableResources() as $resource) {
                        $relation = $resource->associateableName();
                        $countKey = "{$relation}_count";
                        $totalAssociations += $call->getAttributes()[$countKey] ?? 0;
                    }

                    if ($totalAssociations <= 1) {
                        $call->delete();
                    }
                }

                $model->calls()->detach();
            }
        });
    }

    /**
     * Get all of the calls for the model.
     */
    public function calls(): MorphToMany
    {
        return $this->morphToMany(Call::class, 'callable');
    }
}
