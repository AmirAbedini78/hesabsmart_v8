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
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Models\Tag;

class UpdateTagDisplayOrder extends ApiController
{
    /**
     * Save the pipelines display order.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            '*.id' => 'required|int',
            '*.display_order' => 'required|int',
        ]);

        foreach ($request->all() as $tag) {
            Tag::find($tag['id'])->fill(['display_order' => $tag['display_order']])->save();
        }

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
