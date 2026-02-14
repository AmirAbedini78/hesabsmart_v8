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

interface Tokenable
{
    /**
     * Create new client token for the logged in user
     *
     *
     * @return string
     */
    public function newToken(Request $request);
}
