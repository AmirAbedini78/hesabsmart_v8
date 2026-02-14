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

namespace Modules\Core\Http\Controllers\Api\Resource;

use Illuminate\Support\Facades\DB;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ActionRequest;

class ActionController extends ApiController
{
    /**
     * Run resource action.
     */
    public function handle($action, ActionRequest $request): mixed
    {
        $request->performValidation();

        return DB::transaction($request->run(...));
    }
}
