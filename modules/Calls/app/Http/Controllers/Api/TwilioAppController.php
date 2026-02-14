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
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Modules\Core\Http\Controllers\ApiController;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class TwilioAppController extends ApiController
{
    /**
     * Get TwiML Application.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $this->performValidation($request);

        try {
            $app = $this->createClient($request)->applications($id)->fetch();
        } catch (RestException $e) {
            if ($e->getStatusCode() === 404) {
                return $this->response(['deleted' => true], 500);
            }
            throw $e;
        }

        return $this->response($app->toArray());
    }

    /**
     * Update TwiML Application.
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $this->performValidation($request);

        return $this->response(
            $this->createClient($request)->applications($id)->update($request->all())
        );
    }

    /**
     * Create new TwiML Application.
     *
     * @see https://www.twilio.com/docs/phone-numbers/api/incomingphonenumber-resource?code-sample=code-fetch-incoming-phone-number&code-language=PHP&code-sdk-version=6.x
     * @see  https://support.twilio.com/hc/en-us/articles/223135027-Configure-a-Twilio-Phone-Number-to-Receive-and-Respond-to-Voice-Calls
     * @see  https://www.twilio.com/docs/usage/api/applications#list-post-example-1
     */
    public function create(Request $request): JsonResponse
    {
        $data = $this->performValidation($request, ['number' => 'required']);

        $client = $this->createClient($request);

        $incomingPhoneNumbers = $client->incomingPhoneNumbers->read(['phoneNumber' => $data['number']], 1);

        if (count($incomingPhoneNumbers) === 0) {
            abort(Response::HTTP_CONFLICT, 'Incoming phone number not found.');
        }

        $number = $incomingPhoneNumbers[0];

        if ($number->capabilities->getVoice() === false) {
            abort(Response::HTTP_CONFLICT, 'This phone number does not have enabled voice capabilities.');
        }

        $application = $client->applications->create(
            $request->except(['number', 'account_sid', 'auth_token'])
        );

        $client->incomingPhoneNumbers($number->sid)->update([
            'voiceApplicationSid' => $application->sid,
        ]);

        return $this->response([
            'app_sid' => $application->sid,
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Delete TwiML Application.
     */
    public function destroy(string $sid, Request $request): JsonResponse
    {
        $this->performValidation($request);

        try {
            $this->createClient($request)->applications($sid)->delete();
            settings()->forget('twilio_app_sid')->save();
        } catch (RestException $e) {
            if ($e->getStatusCode() !== 404) {
                throw $e;
            }

            settings()->forget('twilio_app_sid')->save();
        }

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Create new Twilio Client from the given request.
     */
    protected function createClient(Request $request): Client
    {
        return new Client(
            $request->input('account_sid', $this->accountSid()),
            $request->input('auth_token', $this->authToken())
        );
    }

    /**
     * Perform common validation.
     */
    protected function performValidation(Request $request, array $data = []): array
    {
        return $request->validate(array_merge($data, [
            'account_sid' => Rule::when(! $this->accountSid(), 'required'),
            'auth_token' => Rule::when(! $this->authToken(), 'required'),
        ]));
    }

    /**
     * Get Twilio account SID.
     */
    protected function accountSid(): ?string
    {
        return config('twilio.accountSid');
    }

    /**
     * Get Twilio auth token.
     */
    protected function authToken(): ?string
    {
        return config('twilio.authToken');
    }
}
