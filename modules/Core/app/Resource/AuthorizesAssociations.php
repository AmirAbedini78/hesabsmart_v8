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

namespace Modules\Core\Resource;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Models\Model;
use Modules\Core\Settings\Utilities\Arr;

trait AuthorizesAssociations
{
    /**
     * Filter the given associations for saving for the given resource
     *
     * @param  string|\Modules\Core\Resource\Resource  $resource  $resource The resource the associations will be attached
     * @param  array  $associations
     * @return array
     */
    protected function authorizeAssociations($resource, $associations)
    {
        $forResource = is_string($resource) ? Innoclapps::resourceByName($resource) : $resource;

        // When the special 'associations' key exists in the associations array, we will merge
        // the 'associations' key with the rest of the associations provided and then continue
        // with all the filtering
        // for example:

        /*

        $associations = ['associations'=>['companies'=>[1,2]]] becomes ['companies'=>[1,2]]

        or

        $associations = [
            'contacts'=>[2,3],
            'companies'=>[2,4],
            'associations'=>['companies'=>[1,2], 'contacts'=>[5]]
        ]

        becomes

        $associations = [
            'contacts'=>[2,3,5],
            'companies'=>[1,2,4,2], (array_unique is performed when quering)
        ]
        */

        return collect($associations)->when(array_key_exists('associations', $associations), function ($collection) {
            return $collection->mergeRecursive($collection['associations'])->forget('associations');
        })->mapWithKeys(function ($values, $resourceName) {
            return [$resourceName => ['values' => Arr::wrap($values), 'resource' => Innoclapps::resourceByName($resourceName)]];
        })->each(function (array $data, string $resourceName) use ($forResource) {
            if (! $data['resource'] || ! $data['resource']->canBeAssociatedTo($forResource)) {
                abort(
                    400,
                    "The provided resource name \"$resourceName\" cannot be associated to the {$forResource->singularLabel()}"
                );
            }
        })->mapWithKeys(function (array $data, string $resourceName) {
            return [$resourceName => $data['resource']->newQuery()->findMany(
                array_unique($data['values'] ?? [])
            )];
        })->map(function (Collection $models) {
            return $models->reject(fn (Model $model) => Gate::denies('view', $model))->modelKeys();
        })->all();
    }
}
