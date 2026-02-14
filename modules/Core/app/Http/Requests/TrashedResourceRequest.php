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

namespace Modules\Core\Http\Requests;

use Illuminate\Database\Eloquent\Builder;

class TrashedResourceRequest extends ResourceRequest
{
    /**
     * Get new query for the current resource.
     */
    public function newQuery(): Builder
    {
        return $this->resource()->newTrashedQuery();
    }
}
