<?php

namespace App\Http\Controllers\Builder;

use App\Models\BuilderDefinition;
use App\Models\BuilderPublishExecution;
use App\Services\Builder\BuilderPublishExecutionPreparationService;
use App\Services\Builder\BuilderPublishStagedFileValidationService;
use App\Services\Builder\BuilderRuntimeWriteBackupArtifactService;
use App\Services\Builder\BuilderRuntimeWriteExecutionPreflightService;
use App\Services\Builder\BuilderRuntimeWritePlanArtifactService;
use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\ApiController;

class BuilderPublishExecutionController extends ApiController
{
    public function index(BuilderDefinition $builderDefinition): JsonResponse
    {
        return $this->response(
            $builderDefinition->publishExecutions()
                ->latest()
                ->paginate(request()->integer('per_page', 15))
        );
    }

    public function store(
        BuilderDefinition $builderDefinition,
        BuilderPublishExecutionPreparationService $preparation
    ): JsonResponse {
        return $this->response($preparation->prepare($builderDefinition), JsonResponse::HTTP_CREATED);
    }

    public function validateStagedFiles(
        BuilderPublishExecution $execution,
        BuilderPublishStagedFileValidationService $validation
    ): JsonResponse {
        return $this->response($validation->validate($execution));
    }

    public function runtimeWritePlan(
        BuilderPublishExecution $execution,
        BuilderRuntimeWritePlanArtifactService $planner
    ): JsonResponse {
        return $this->response($planner->plan($execution));
    }

    public function runtimeWritePreflight(
        BuilderPublishExecution $execution,
        BuilderRuntimeWriteExecutionPreflightService $preflight
    ): JsonResponse {
        return $this->response($preflight->preflight($execution));
    }

    public function runtimeWriteBackups(
        BuilderPublishExecution $execution,
        BuilderRuntimeWriteBackupArtifactService $backups
    ): JsonResponse {
        return $this->response($backups->prepare($execution));
    }
}
