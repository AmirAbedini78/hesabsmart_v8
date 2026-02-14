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

use Modules\Core\Contracts\Resources\Resourceable;
use Modules\Core\Models\Model;

class EmailSearch extends GlobalSearch
{
    /**
     * Provide the model data for the response.
     */
    protected function data(Model&Resourceable $model, Resource $resource): array
    {
        /** @var \Modules\Core\Contracts\Resources\HasEmail&\Modules\Core\Resource\Resource $resource */

        return [
            'id' => $model->getKey(),
            'address' => $model->{$resource->emailAddressField()},
            'name' => $resource->titleFor($model),
            'path' => $resource->viewRouteFor($model),
            'resourceName' => $resource->name(),
        ];
    }
}
