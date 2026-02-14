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

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Resources\CountryResource;
use Modules\Core\Models\Country;

class RetrieveCountries extends ApiController
{
    /**
     * Get a list of all the application countries in storage.
     */
    public function __invoke(): JsonResponse
    {
        return $this->response(
            CountryResource::collection(Country::get())
        );
    }
}
