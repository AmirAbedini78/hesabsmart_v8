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

namespace Modules\WebForms\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\ApiController;
use Modules\WebForms\Http\Resources\WebFormResource;
use Modules\WebForms\Models\WebForm;

class CloneWebForm extends ApiController
{
    /**
     * Clone the web form.
     */
    public function __invoke(WebForm $form, Request $request): JsonResponse
    {
        $clonedForm = tap($form->replicate(['total_submissions', 'created_by', 'user_id', 'uuid'])->forceFill([
            'title' => clone_prefix($form->title),
            'user_id' => $request->user()->id,
            'total_submissions' => 0,
        ]))->save();

        return $this->response(
            new WebFormResource($clonedForm)
        );
    }
}
