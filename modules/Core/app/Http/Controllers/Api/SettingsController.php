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
use Illuminate\Support\Str;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\SettingRequest;

class SettingsController extends ApiController
{
    /**
     * Get the application settings.
     */
    public function index(): JsonResponse
    {
        return $this->response(
            collect(settings()->all())->reject(fn ($value, $name) => Str::startsWith($name, '_'))
        );
    }

    /**
     * Persist the settings in storage.
     */
    public function save(SettingRequest $request): JsonResponse
    {
        $request->saveSettings();

        return $this->response(settings()->all());
    }
}
