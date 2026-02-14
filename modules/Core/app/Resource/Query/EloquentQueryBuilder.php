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

namespace Modules\Core\Resource\Query;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Contracts\Resources\AcceptsCustomFields;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Resource\Resource;

class EloquentQueryBuilder extends Builder
{
    /**
     * Set the relationships that should be eager loaded.
     *
     * The method ensures when regularly using $query->with('resourceRelationWithCustomFields')
     * is loading the resource optionable custom fields to avoid eager loading violation.
     *
     * @param  string|array  $relations
     * @param  string|\Closure|null  $callback
     * @return $this
     */
    public function with($relations, $callback = null)
    {
        $instance = parent::with(...func_get_args());

        foreach (array_keys($this->eagerLoad) as $relation) {
            // We do not deep load optionable custom fields.
            if (str_contains($relation, '.')) {
                continue;
            }

            $relatedModel = $this->getModel()->{$relation}()->getModel();

            /**
             * @var \Modules\Core\Resource\Resource|(\Modules\Core\Resource\Resource&\Modules\Core\Contracts\Resources\AcceptsCustomFields)
             */
            $relatedResource = Innoclapps::resourceByModel($relatedModel);

            if ($relatedResource instanceof AcceptsCustomFields) {
                $this->withResourceRelationCustomFields($relation, $relatedResource);
            }
        }

        return $instance;
    }

    /**
     * Eager load resource relation custom fields.
     */
    protected function withResourceRelationCustomFields(string $relation, Resource $resource): void
    {
        $eagerLoad = $resource
            ->customFields()
            ->optionable()
            ->pluck('relationName')
            ->map(fn (string $customFieldRelation) => $relation.'.'.$customFieldRelation)
            ->all();

        // Ensure to add/override the original relation we are adding the custom fields
        // relation for eager loading, as select statement don't work if it's not added.
        // Model::with(['someRelationWithCustomFields' => function ($query) {
        //     // This won't work
        //     $query->select(['id', 'some_field']);
        // }])->get();
        $eagerLoad[$relation] = $this->eagerLoad[$relation];

        parent::with($eagerLoad);
    }
}
