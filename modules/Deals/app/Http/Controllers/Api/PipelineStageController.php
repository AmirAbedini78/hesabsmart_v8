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

namespace Modules\Deals\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Deals\Http\Resources\StageResource;
use Modules\Deals\Models\Pipeline;
use Modules\Deals\Models\Stage;

class PipelineStageController extends ApiController
{
    /**
     * Retrieve pipeline stages.
     */
    public function index(Pipeline $pipeline, Request $request): JsonResponse
    {
        $this->authorize('view', $pipeline);

        return $this->response(
            StageResource::collection(
                Stage::where('pipeline_id', $pipeline->id)->paginate($request->perPage())
            )
        );
    }
}
