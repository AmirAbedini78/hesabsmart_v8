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
use Modules\Core\Models\DataView;

class DataViewUserConfigController extends ApiController
{
    /**
     * Set the open states of the data views for the current user.
     */
    public function open(string $identifier, Request $request): JsonResponse
    {
        $views = DataView::forUser($request->user(), $identifier)->find($request->keys());

        if ($request->collect()->filter(fn (bool $isOpen) => $isOpen === true)->count() > config('core.views.max_open')) {
            abort(
                JsonResponse::HTTP_CONFLICT,
                sprintf('The maximum allowed number of open views is %s.', config('core.views.max_open'))
            );
        }

        $views->each(
            fn (DataView $view) => $view->markAsOpen($request->user(), $request->input($view->id))
        );

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Set the order of the data views for the current user.
     */
    public function order(string $identifier, Request $request): JsonResponse
    {
        $views = DataView::forUser($request->user(), $identifier)->find($request->keys());

        $views->each(
            fn (DataView $view) => $view->setOrder($request->user(), $request->input($view->id))
        );

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
