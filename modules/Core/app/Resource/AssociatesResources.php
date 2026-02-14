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

use Modules\Core\Facades\Innoclapps;

trait AssociatesResources
{
    use AuthorizesAssociations;

    /**
     * Attach the given associations to the given resource
     *
     * @param  string|\Modules\Core\Resource\Resource  $resource
     * @param  int  $primaryRecordId
     * @param  array  $associations
     * @return void
     */
    protected function attachAssociations($resource, $primaryRecordId, $associations)
    {
        $this->saveAssociations($resource, $primaryRecordId, $associations, 'attach');
    }

    /**
     * Sync the given associations to the given resource
     *
     * @param  string|\Modules\Core\Resource\Resource  $resource
     * @param  int  $primaryRecordId
     * @param  array  $associations
     * @return void
     */
    protected function syncAssociations($resource, $primaryRecordId, $associations)
    {
        $this->saveAssociations($resource, $primaryRecordId, $associations, 'sync');
    }

    /**
     * Sync the given associations to the given resource
     *
     * @param  string|\Modules\Core\Resource\Resource  $resource
     * @param  int  $primaryRecordId
     * @param  array  $associations
     * @param  string  $method
     * @return void
     */
    protected function saveAssociations($resource, $primaryRecordId, $associations, $method)
    {
        $forResource = is_string($resource) ? Innoclapps::resourceByName($resource) : $resource;

        foreach ($associations as $resourceName => $ids) {
            if (! is_array($ids)) {
                continue;
            }

            // [ 'associations' => [ 'contacts' => [1,2] ]]
            if ($resourceName === 'associations') {
                $this->saveAssociations($forResource, $primaryRecordId, $associations, $method);

                continue;
            }

            $forResource->newModel()
                ->find($primaryRecordId)
                ->{Innoclapps::resourceByName($resourceName)->associateableName()}()
                ->{$method}($ids);
        }
    }
}
