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
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Resources\MailableTemplateResource;
use Modules\Core\Models\MailableTemplate;
use Modules\Core\Rules\StringRule;

class MailableTemplateController extends ApiController
{
    /**
     * Retrieve all of the available mailable templates.
     */
    public function index(): JsonResponse
    {
        MailableTemplates::seed();

        $collection = MailableTemplateResource::collection(MailableTemplate::orderBy('name')->get());

        return $this->response($collection);
    }

    /**
     * Retrieve mail templates in specific locale.
     */
    public function forLocale(string $locale): JsonResponse
    {
        MailableTemplates::seed();

        $collection = MailableTemplateResource::collection(
            MailableTemplate::orderBy('name')->forLocale($locale)->get()
        );

        return $this->response($collection);
    }

    /**
     * Display the specified resource.
     */
    public function show(MailableTemplate $template): JsonResponse
    {
        return $this->response(new MailableTemplateResource($template));
    }

    /**
     * Update the specified mail template in storage.
     */
    public function update(MailableTemplate $template, Request $request): JsonResponse
    {
        $request->validate([
            'subject' => ['required', StringRule::make()],
            'html_template' => 'required|string',
        ]);

        $template->fill($request->all())->save();

        return $this->response(new MailableTemplateResource($template));
    }
}
