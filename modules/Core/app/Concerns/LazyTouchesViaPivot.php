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

use GeneaLabs\LaravelPivotEvents\Traits\PivotEventTrait;

/** @mixin \Modules\Core\Models\Model */
trait LazyTouchesViaPivot
{
    use PivotEventTrait;

    /**
     * Provide the related pivot relationships to touch.
     *
     * When any of the provided relationships are changed for this model,
     * we will touch the relationships, additionally, the main pivot will be touched.
     */
    protected function relatedPivotRelationsToTouch(): array
    {
        return [];
    }

    /**
     * Boot the LazyTouchesViaPivot trait.
     */
    protected static function bootLazyTouchesViaPivot(): void
    {
        static::pivotAttached(function (self $model, string $relationName, array $pivotIds) {
            $model->touchIfTouchingViaPivot($model, $relationName, $pivotIds);
        });

        static::pivotDetached(function (self $model, string $relationName, array $pivotIds) {
            $model->touchIfTouchingViaPivot($model, $relationName, $pivotIds);
        });

        static::pivotSynced(function (self $model, string $relationName, array $changes) {
            $pivotIds = array_merge(...array_values($changes));
            $model->touchIfTouchingViaPivot($model, $relationName, $pivotIds);
        });
    }

    /**
     * Touch the main model and parent pivot models if needed.
     */
    protected function touchIfTouchingViaPivot(self $model, string $relationName, array $pivotIds): void
    {
        if (count($pivotIds) === 0 ||
            method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
        ) {
            return;
        }

        // We will check if the main model is ignoring touch, if yes, won't touch
        // next we will check whether previously the model was saved and the "updated_at"
        // column was changed, in this case, no need for additional query to touch.
        if (! $model->wasRecentlyCreated && ! $model::isIgnoringTouch() && ! $model->wasChanged($model->getUpdatedAtColumn())) {
            $model->touchQuietly();
        }

        $relatedModel = $model->{$relationName}()->getModel();

        if (
            in_array($relationName, $this->relatedPivotRelationsToTouch()) &&
            ! $relatedModel::isIgnoringTouch()
        ) {
            $relatedModel->newQueryWithoutRelationships()
                ->whereKey($pivotIds)
                ->update([$relatedModel->getUpdatedAtColumn() => $relatedModel->freshTimestampString()]);
        }
    }
}
