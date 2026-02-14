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

namespace Modules\MailClient\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Criteria\RequestCriteria;
use Modules\Core\Http\Controllers\ApiController;
use Modules\MailClient\Http\Requests\PredefinedMailTemplateRequest;
use Modules\MailClient\Http\Resources\PredefinedMailTemplateResource;
use Modules\MailClient\Models\PredefinedMailTemplate;

class PredefinedMailTemplateController extends ApiController
{
    /**
     * Display a listing of the mail templates.
     */
    public function index(): JsonResponse
    {
        $result = PredefinedMailTemplate::with('user')
            ->visibleToUser()
            ->criteria(
                (new RequestCriteria)->setSearchFields(['name' => 'like', 'subject'])
            )
            ->get();

        return $this->response(PredefinedMailTemplateResource::collection(
            $result
        ));
    }

    /**
     * Display the specified mail template.
     */
    public function show(string $id): JsonResponse
    {
        $template = PredefinedMailTemplate::with('user')->findOrFail($id);

        $this->authorize('view', $template);

        return $this->response(new PredefinedMailTemplateResource($template));
    }

    /**
     * Store a newly created mail template in storage.
     */
    public function store(PredefinedMailTemplateRequest $request): JsonResponse
    {
        $template = PredefinedMailTemplate::create(
            $request->merge(['user_id' => $request->user()->id])->all()
        );

        return $this->response(
            new PredefinedMailTemplateResource($template->load('user')),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Update the specified mail template in storage.
     */
    public function update(string $id, PredefinedMailTemplateRequest $request): JsonResponse
    {
        $template = PredefinedMailTemplate::findOrFail($id);

        $this->authorize('update', $template);

        $template->fill($request->except('user_id'))->save();

        return $this->response(
            new PredefinedMailTemplateResource($template->load('user'))
        );
    }

    /**
     * Remove the specified mail template from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $template = PredefinedMailTemplate::findOrFail($id);

        $this->authorize('delete', $template);

        $template->delete();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
