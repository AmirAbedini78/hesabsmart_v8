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

namespace Modules\Documents\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Documents\Enums\DocumentStatus;
use Modules\Documents\Http\Resources\DocumentResource;
use Modules\Documents\Models\Document;

class DocumentStateController extends ApiController
{
    /**
     * Mark the document as Lost.
     */
    public function lost(Document $document, Request $request): JsonResponse
    {
        $this->authorize('update', $document);

        if ($document->status === DocumentStatus::LOST) {
            abort(JsonResponse::HTTP_CONFLICT, 'This document is already marked as lost.');
        } elseif ($document->status === DocumentStatus::ACCEPTED) {
            abort(JsonResponse::HTTP_CONFLICT, 'You cannot mark accepted document as lost.');
        }

        $document->markAsLost($request->user());

        return $this->response(
            new DocumentResource($document->resource()->displayQuery()->find($document->id))
        );
    }

    /**
     * Mark the document as accepted.
     */
    public function accept(Document $document, Request $request): JsonResponse
    {
        $this->authorize('update', $document);

        if ($document->status === DocumentStatus::ACCEPTED) {
            abort(JsonResponse::HTTP_CONFLICT, 'This document is already accepted.');
        }

        $document->markAsAccepted($request->user());

        return $this->response(
            new DocumentResource($document->resource()->displayQuery()->find($document->id))
        );
    }

    /**
     * Mark the document as draft.
     */
    public function draft(Document $document, Request $request): JsonResponse
    {
        $this->authorize('update', $document);

        if ($document->status !== DocumentStatus::LOST &&
        $document->status === DocumentStatus::ACCEPTED && ! $document->marked_accepted_by) {
            if ($document->status === DocumentStatus::ACCEPTED) {
                abort(JsonResponse::HTTP_CONFLICT, 'Documents signed/accepted by customers cannot be marked as draft.');
            } else {
                abort(JsonResponse::HTTP_CONFLICT, 'Only lost documents can be marked as draft.');
            }
        }

        $document->markAsDraft($request->user());

        return $this->response(
            new DocumentResource($document->resource()->displayQuery()->find($document->id))
        );
    }
}
