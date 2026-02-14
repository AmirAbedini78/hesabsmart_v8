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

namespace Modules\Core\Common\VisibilityGroup;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Core\Models\Model;
use Modules\Core\Models\ModelVisibilityGroupDependent;

/** @mixin \Modules\Core\Models\Model */
trait VisibilityDependentable
{
    /**
     * Boot the "VisibilityDependentable" trait.
     */
    protected static function bootVisibilityDependentable(): void
    {
        static::deleting(function (Model $model) {
            if ($model->isReallyDeleting()) {
                $model->visibilityDependents()->delete();
            }
        });
    }

    /**
     * Get all of the visibility dependent models.
     */
    public function visibilityDependents(): MorphMany
    {
        return $this->morphMany(ModelVisibilityGroupDependent::class, 'dependable');
    }
}
