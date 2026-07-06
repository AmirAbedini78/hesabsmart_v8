<?php

namespace App\Http\Controllers\Builder;

use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteOperatorAcknowledgement;
use App\Services\Builder\BuilderRuntimeWriteOperatorAcknowledgementService;
use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\ApiController;

class BuilderRuntimeWriteOperatorAcknowledgementController extends ApiController
{
    public function index(
        BuilderPublishExecution $execution,
        BuilderRuntimeWriteOperatorAcknowledgementService $service
    ): JsonResponse {
        return $this->response($service->listForExecution($execution));
    }

    public function store(
        BuilderPublishExecution $execution,
        BuilderRuntimeWriteOperatorAcknowledgementService $service
    ): JsonResponse {
        return $this->response($service->request($execution), JsonResponse::HTTP_CREATED);
    }

    public function acknowledge(
        BuilderRuntimeWriteOperatorAcknowledgement $acknowledgement,
        BuilderRuntimeWriteOperatorAcknowledgementService $service
    ): JsonResponse {
        return $this->response($service->acknowledge($acknowledgement, request('note')));
    }

    public function revoke(
        BuilderRuntimeWriteOperatorAcknowledgement $acknowledgement,
        BuilderRuntimeWriteOperatorAcknowledgementService $service
    ): JsonResponse {
        return $this->response($service->revoke($acknowledgement, request('note')));
    }
}
