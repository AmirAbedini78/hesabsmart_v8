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

namespace Modules\Core\Http\Controllers\Api\Module;

use Illuminate\Http\JsonResponse;
use Modules\Core\Facades\Module as ModuleFacade;
use Modules\Core\Http\Controllers\ApiController;

class ModuleStatusController extends ApiController
{
    /**
     * Enable the given module.
     */
    public function enable(string $module): JsonResponse
    {
        $module = ModuleFacade::findOrFail($module);

        if ($module->isDisabled()) {
            $module->enable();
        }

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Disable the given module.
     */
    public function disable(string $name): JsonResponse
    {
        $module = ModuleFacade::findOrFail($name);

        if ($module->isCore()) {
            abort(JsonResponse::HTTP_CONFLICT, 'Core modules cannot be disabled.');
        }

        if ($module->isEnabled()) {
            $module->disable();
        }

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
