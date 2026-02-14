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

namespace Modules\Users\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Core\Http\Controllers\ApiController;

class NotificationController extends ApiController
{
    /**
     * List current user notifications.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->response(
            $request->user()->notifications()->paginate($request->perPage())
        );
    }

    /**
     * Retrieve current user notification.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        return $this->response(
            $request->user()->notifications()->find($id)
        );
    }

    /**
     * Set all notifications for current user as read.
     */
    public function update(Request $request, ?string $id = ''): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->when($id !== '', fn (Builder $query) => $query->where('id', $id))
            ->update(['read_at' => now()]);

        return $this->response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete current user notification
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $request->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        return $this->response('', Response::HTTP_NO_CONTENT);
    }
}
