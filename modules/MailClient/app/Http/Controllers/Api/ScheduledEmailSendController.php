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
use Modules\MailClient\Criteria\EmailAccountsForUserCriteria;
use Modules\MailClient\Models\ScheduledEmail;

class ScheduledEmailSendController extends ApiController
{
    /**
     * Send the given scheduled email.
     */
    public function __invoke(string $id): JsonResponse
    {
        $message = ScheduledEmail::withWhereHas(
            'account', fn ($query) => $query->criteria(EmailAccountsForUserCriteria::class)
        )->findOrFail($id);

        if ($message->isSending()) {
            abort(JsonResponse::HTTP_CONFLICT, 'This email is already being sent in background.');
        } elseif ($message->isSent()) {
            abort(JsonResponse::HTTP_CONFLICT, 'This email is already sent.');
        }

        $message->send();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
