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

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MediaUploader;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Resources\MediaResource;
use Modules\Core\Models\PendingMedia;
use Plank\Mediable\Exceptions\MediaUploadException;
use Plank\Mediable\HandlesMediaUploadExceptions;

class PendingMediaController extends ApiController
{
    use HandlesMediaUploadExceptions;

    /**
     * Upload pending media.
     */
    public function store(string $draftId, Request $request): JsonResponse
    {
        try {
            $media = MediaUploader::fromSource($request->file('file'))
                ->toDirectory('pending-attachments')
                ->setAllowedExtensions(Innoclapps::allowedUploadExtensions())
                ->upload();

            $media->markAsPending($draftId);
        } catch (MediaUploadException $e) {
            /** @var \Symfony\Component\HttpKernel\Exception\HttpException */
            $exception = $this->transformMediaUploadException($e);

            return $this->response(['message' => $exception->getMessage()], $exception->getStatusCode());
        }

        return $this->response(new MediaResource($media->load('pendingData')), JsonResponse::HTTP_CREATED);
    }

    /**
     * Delete pending media attachment.
     */
    public function destroy(string $pendingMediaId): JsonResponse
    {
        PendingMedia::findOrFail($pendingMediaId)->purge();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
