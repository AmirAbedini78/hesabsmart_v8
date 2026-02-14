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
use Modules\MailClient\Http\Resources\EmailAccountMessageResource;
use Modules\MailClient\Models\EmailAccountMessage;

class EmailAccountMessageTagController extends ApiController
{
    /**
     * Sync tags for the given message.
     */
    public function __invoke(string $messageId, Request $request): JsonResponse
    {
        $message = EmailAccountMessage::find($messageId);

        $this->authorize('update', $message);

        $message->syncTagsWithType($request->input('tags', []), EmailAccountMessage::TAGS_TYPE);
        $message->load('tags');

        return $this->response(new EmailAccountMessageResource(
            $message
        ));
    }
}
