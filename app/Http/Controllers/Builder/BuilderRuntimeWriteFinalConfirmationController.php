<?php

namespace App\Http\Controllers\Builder;

use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteFinalConfirmation;
use App\Services\Builder\BuilderRuntimeWriteFinalConfirmationService;
use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\ApiController;

class BuilderRuntimeWriteFinalConfirmationController extends ApiController
{
    public function index(
        BuilderPublishExecution $execution,
        BuilderRuntimeWriteFinalConfirmationService $service
    ): JsonResponse {
        return $this->response($service->listForExecution($execution));
    }

    public function store(
        BuilderPublishExecution $execution,
        BuilderRuntimeWriteFinalConfirmationService $service
    ): JsonResponse {
        return $this->response($service->request($execution), JsonResponse::HTTP_CREATED);
    }

    public function grant(
        BuilderRuntimeWriteFinalConfirmation $confirmation,
        BuilderRuntimeWriteFinalConfirmationService $service
    ): JsonResponse {
        return $this->response($service->grant($confirmation, request('note')));
    }

    public function reject(
        BuilderRuntimeWriteFinalConfirmation $confirmation,
        BuilderRuntimeWriteFinalConfirmationService $service
    ): JsonResponse {
        return $this->response($service->reject($confirmation, request('note')));
    }

    public function revoke(
        BuilderRuntimeWriteFinalConfirmation $confirmation,
        BuilderRuntimeWriteFinalConfirmationService $service
    ): JsonResponse {
        return $this->response($service->revoke($confirmation, request('note')));
    }
}
