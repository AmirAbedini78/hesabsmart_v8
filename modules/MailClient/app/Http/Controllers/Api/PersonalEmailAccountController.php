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

namespace Modules\MailClient\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\ApiController;
use Modules\MailClient\Http\Resources\EmailAccountResource;
use Modules\MailClient\Models\EmailAccount;

class PersonalEmailAccountController extends ApiController
{
    /**
     * Display personal email accounts for the logged in user.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $accounts = EmailAccount::withCommon()
            ->personal((int) $request->user()->id)
            ->orderBy('email')
            ->get();

        return $this->response(
            EmailAccountResource::collection($accounts)
        );
    }
}
