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

use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Resource\Resource;
use Modules\Core\Table\Table;

class ResourceTableRequest extends ResourceRequest
{
    /**
     * Get the class of the resource being requested.
     */
    public function resource(): Resource
    {
        return tap(parent::resource(), function ($resource) {
            abort_if(! $resource instanceof Tableable, 404);
        });
    }

    /**
     * Resolve the resource table for the current request.
     */
    public function resolveTable(): Table
    {
        return $this->resource()->resolveTable($this);
    }

    /**
     * Resolve the resource trashed table for the current request.
     */
    public function resolveTrashedTable(): Table
    {
        return $this->resource()->resolveTrashedTable($this);
    }
}
