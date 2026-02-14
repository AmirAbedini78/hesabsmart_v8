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

namespace Modules\Core\Http\Controllers\Api;

use Akaunting\Money\Currency;
use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\ApiController;

class RetrieveCurrencies extends ApiController
{
    /**
     * Get the application available currencies.
     */
    public function __invoke(): JsonResponse
    {
        return $this->response(Currency::getCurrencies());
    }
}
