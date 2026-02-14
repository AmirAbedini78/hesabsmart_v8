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
use Modules\Core\Http\Controllers\ApiController;
use Modules\MailClient\Http\Resources\EmailAccountResource;
use Modules\MailClient\Models\EmailAccount;

class SharedEmailAccountController extends ApiController
{
    /**
     * Display shared email accounts.
     */
    public function __invoke(): JsonResponse
    {
        $accounts = EmailAccount::withCommon()
            ->shared()
            ->orderBy('email')
            ->get();

        return $this->response(
            EmailAccountResource::collection($accounts)
        );
    }
}
