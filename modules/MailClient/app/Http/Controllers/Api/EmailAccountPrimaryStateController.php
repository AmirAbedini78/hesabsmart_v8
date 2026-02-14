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
use Modules\MailClient\Models\EmailAccount;

class EmailAccountPrimaryStateController extends ApiController
{
    /**
     * Mark the given account as primary for the current user.
     */
    public function update(string $id): JsonResponse
    {
        /** @var \Modules\MailClient\Models\EmailAccount */
        $account = EmailAccount::findOrFail($id);

        $this->authorize('view', $account);

        /** @var \Modules\Users\Model\User&\Modules\Core\Contracts\Metable */
        $user = auth()->user();

        $account->markAsPrimary($user);

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Remove primary account for the current user.
     */
    public function destroy(): JsonResponse
    {
        /** @var \Modules\Users\Model\User&\Modules\Core\Contracts\Metable */
        $user = auth()->user();

        EmailAccount::unmarkAsPrimary($user);

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
