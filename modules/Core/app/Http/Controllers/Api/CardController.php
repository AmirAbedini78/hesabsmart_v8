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
use Modules\Core\Facades\Cards;
use Modules\Core\Http\Controllers\ApiController;

class CardController extends ApiController
{
    /**
     * Get cards that are intended to be shown on dashboards.
     */
    public function forDashboards(): JsonResponse
    {
        return $this->response(Cards::resolveForDashboard());
    }

    /**
     * Get the available cards for a given resource.
     */
    public function index(string $resourceName): JsonResponse
    {
        return $this->response(Cards::resolve($resourceName));
    }

    /**
     * Get card by given uri key.
     */
    public function show(string $card): JsonResponse
    {
        return $this->response(Cards::registered()->first(function ($item) use ($card) {
            return $item->uriKey() === $card;
        })->authorizeOrFail());
    }
}
