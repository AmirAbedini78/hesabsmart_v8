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

namespace Modules\Calls\VoIP\Contracts;

use Illuminate\Http\Request;
use Modules\Calls\VoIP\Call;

interface VoIPClient
{
    /**
     * Validate the request for authenticity
     *
     *
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function validateRequest(Request $request);

    /**
     * Create new outgoing call from request
     *
     * @param  string  $phoneNumber
     * @return mixed
     */
    public function newOutgoingCall($phoneNumber, Request $request);

    /**
     * Create new incoming call from request
     *
     *
     * @return mixed
     */
    public function newIncomingCall(Request $request);

    /**
     * Get the Call class from the given webhook request
     */
    public function getCall(Request $request): Call;
}
