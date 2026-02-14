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
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\DataViewRequest;
use Modules\Core\Http\Resources\DataViewResource;
use Modules\Core\Models\DataView;

class DataViewController extends ApiController
{
    /**
     * Get views from storage by identifier for logged in user.
     */
    public function index(string $identifier, Request $request): JsonResponse
    {
        $views = DataView::forUser($request->user(), $identifier)->get();

        return $this->response(
            DataViewResource::collection($views)
        );
    }

    /**
     * Create view in storage.
     */
    public function store(DataViewRequest $request): JsonResponse
    {
        $view = new DataView($request->merge(['user_id' => $request->user()->id])->all());

        $view->save();

        return $this->response(new DataViewResource($view), JsonResponse::HTTP_CREATED);
    }

    /**
     * Update given view.
     */
    public function update(DataView $view, DataViewRequest $request): JsonResponse
    {
        $this->authorize('update', $view);

        $attributes = $view->isSystemDefault() ?
            $request->only('config') :
            $request->except(['identifier', 'user_id']);

        $view->fill($attributes)->save();

        return $this->response(new DataViewResource($view));
    }

    /**
     * Delete given view.
     */
    public function destroy(DataView $view): JsonResponse
    {
        $this->authorize('delete', $view);

        if ($view->isSystemDefault()) {
            abort(403, 'Application default views cannot be deleted.');
        }

        $view->delete();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
