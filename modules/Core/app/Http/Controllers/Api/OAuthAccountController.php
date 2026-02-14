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
use Modules\Core\Http\Resources\OAuthAccountResource;
use Modules\Core\Models\OAuthAccount;

class OAuthAccountController extends ApiController
{
    /**
     * Get the user connected OAuth accounts.
     */
    public function index(Request $request): JsonResponse
    {
        $collection = OAuthAccountResource::collection(
            OAuthAccount::where('user_id', $request->user()->id)->get()
        );

        return $this->response($collection);
    }

    /**
     * Display the specified oauth account.
     */
    public function show(OAuthAccount $account): JsonResponse
    {
        $this->authorize('view', $account);

        return $this->response(new OAuthAccountResource($account));
    }

    /**
     * Remove the specified account from storage.
     */
    public function destroy(OAuthAccount $account): JsonResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
