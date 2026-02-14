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
use Modules\Core\Rules\StringRule;

class PersonalAccessTokenController extends ApiController
{
    /**
     * Get all user personal access tokens.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->response($request->user()->tokens);
    }

    /**
     * Create new user personal access token.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', StringRule::make()],
        ]);

        return $this->response(
            $request->user()->createToken($request->name),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Revoke the given user personal access token.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $request->user()->tokens()->findOrFail($id)->delete();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
