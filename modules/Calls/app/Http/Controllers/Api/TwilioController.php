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

namespace Modules\Calls\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\ApiController;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class TwilioController extends ApiController
{
    /**
     * Retrieve available incoming phone numbers.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $numbers = (new Client(
                $request->input('account_sid'),
                $request->input('auth_token')
            ))->incomingPhoneNumbers->read([], 50);
        } catch (RestException $e) {
            abort(409, $e->getMessage());
        }

        return $this->response(
            collect($numbers)->map(function ($number) {
                return $number->toArray();
            })->all()
        );
    }

    /**
     * Disconnect the Twilio Integration.
     */
    public function destroy(): JsonResponse
    {
        $appId = settings('twilio_app_sid');
        $accountId = settings('twilio_account_sid');
        $authToken = settings('twilio_auth_token');

        if (! empty($appId) && ! empty($accountId) && ! empty($authToken)) {
            try {
                (new Client($accountId, $authToken))->applications($appId)->delete();
            } catch (RestException $e) {
            }
        }

        settings()->forget([
            'twilio_auth_token', 'twilio_account_sid', 'twilio_app_sid', 'twilio_number',
        ])->save();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
