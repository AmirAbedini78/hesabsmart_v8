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

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Users\Http\Resources\UserResource;
use Modules\Users\Models\User;
use Modules\Users\Services\UserAvatarService;

class UserAvatarController extends ApiController
{
    /**
     * Upload user avatar.
     */
    public function store(Request $request, UserAvatarService $service): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|max:1024',
        ]);

        $user = $service->store($request->user(), $request->file('avatar'));

        return $this->response(new UserResource(
            User::withCommon()->find($user->id)
        ));
    }

    /**
     * Delete the user avatar.
     */
    public function delete(Request $request, UserAvatarService $service): JsonResponse
    {
        $user = $request->user();

        $service::remove($user);

        $user->fill(['avatar' => null])->save();

        return $this->response(new UserResource(
            User::withCommon()->find($user->id)
        ));
    }
}
