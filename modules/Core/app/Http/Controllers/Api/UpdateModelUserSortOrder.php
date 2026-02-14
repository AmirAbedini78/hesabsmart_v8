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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Core\Concerns\UserSortable;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Support\ModelFinder;

class UpdateModelUserSortOrder extends ApiController
{
    /**
     * Pin the given timelineable to the given resource.
     */
    public function __invoke(string $model, Request $request, ModelFinder $finder): JsonResponse
    {
        $request->validate([
            'module' => 'required|string',
            'order.*.id' => 'required|int',
            'order.*.display_order' => 'required|int',
        ]);

        $model = collect($finder->in(
            module_path($request->input('module'), config('modules.paths.generator.model.path'))
        )->find())->first(fn (string $class) => Str::endsWith($class, Str::studly($model)));

        abort_if(is_null($model) || ! in_array(UserSortable::class, class_uses_recursive($model)), 404);

        foreach ($request->input('order', []) as $data) {
            $model::find($data['id'])->saveUserSortOrder($request->user(), $data['display_order']);
        }

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
