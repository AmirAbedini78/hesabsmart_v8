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
use Illuminate\Support\Facades\DB;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\TrashedResourceRequest;
use Modules\Core\Models\Model;

class EmptyTrash extends ApiController
{
    /**
     * Empth the resource trashed records.
     *
     * The request must be made in batches until there are no records available.
     * 99% of customers does not use queue, hence, we cannot queue a job.
     */
    public function __invoke(TrashedResourceRequest $request): JsonResponse
    {
        $totalDeleted = 0;

        DB::transaction(function () use ($request, &$totalDeleted) {
            $request->resource()
                ->trashedIndexQuery($request)
                ->limit($request->integer('limit', 500))
                ->get()
                ->filter(fn ($model) => $request->user()->can('bulkDelete', $model))
                ->each(function (Model $model) use (&$totalDeleted) {
                    if ($model->forceDelete()) {
                        $totalDeleted++;
                    }
                });
        });

        return $this->response(['deleted' => $totalDeleted]);
    }
}
