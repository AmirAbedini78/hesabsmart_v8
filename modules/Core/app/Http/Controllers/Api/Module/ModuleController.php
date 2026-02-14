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
use Modules\Core\Module\Module;

class ModuleController extends ApiController
{
    /**
     * Get all of the available modules.
     */
    public function index(): JsonResponse
    {
        return $this->response(collect(ModuleFacade::all())->map(function (Module $module) {
            return [
                'name' => $module->getName(),
                'lower_name' => $module->getLowerName(),
                'version' => $module->version(),
                'is_core' => $module->isCore(),
                'description' => clean($module->get('description')),
                'disabled' => $module->isDisabled(),
            ];
        })->values());
    }

    /**
     * Delete a module.
     */
    public function destroy(string $name): JsonResponse
    {
        $module = ModuleFacade::findOrFail($name);

        if ($module->isCore()) {
            abort(JsonResponse::HTTP_CONFLICT, 'Cannot delete core modules.');
        }

        if ($module->isEnabled()) {
            abort(JsonResponse::HTTP_CONFLICT, 'The module need to be disabled before deletion.');
        }

        $module->purge();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
