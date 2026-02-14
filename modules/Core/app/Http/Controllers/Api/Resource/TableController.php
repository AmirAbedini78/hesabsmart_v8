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

namespace Modules\Core\Http\Controllers\Api\Resource;

use Illuminate\Http\JsonResponse;
use Modules\Core\Filters\Exceptions\QueryBuilderException;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceTableRequest;
use Modules\Core\Http\Resources\TableResource;

class TableController extends ApiController
{
    /**
     * Display a table listing of the resource.
     */
    public function index(ResourceTableRequest $request): JsonResponse
    {
        try {
            $table = $request->boolean('trashed') ?
                $request->resolveTrashedTable() :
                $request->resolveTable();

            return $this->response(
                TableResource::collection($table->result())->additional(['meta' => array_merge([
                    'pre_total' => $table->preTotal,
                ], $table->meta)])
            );
        } catch (QueryBuilderException $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * Get the resource table settings.
     */
    public function settings(ResourceTableRequest $request): JsonResponse
    {
        return $this->response(
            $request->boolean('trashed') ?
            $request->resolveTrashedTable()->settings() :
            $request->resolveTable()->settings()
        );
    }
}
