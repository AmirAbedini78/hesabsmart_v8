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

namespace Modules\WebForms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\WebForms\Http\Requests\WebFormRequest;
use Modules\WebForms\Http\Resources\WebFormResource;
use Modules\WebForms\Models\WebForm;
use Modules\WebForms\Services\FormSubmissionService;

class WebFormController extends Controller
{
    /**
     * Display the web form.
     */
    public function show(string $uuid, Request $request): View
    {
        $form = WebForm::findByUuid($uuid);

        // Change the locale in case the fields are using the translation
        // function so the data can be properly shown
        // @todo, check this, perhaps not needed?
        app()->setLocale($form->locale);

        $form->addFieldToFieldSections();

        abort_if(! Auth::check() && ! $form->isActive(), 404);

        $form = new WebFormResource($form);
        $title = $form->sections[0]['title'] ?? __('webforms::form.form');

        return view('webforms::view', compact('form', 'title'));
    }

    /**
     * Process the webform request.
     */
    public function store(string $uuid, FormSubmissionService $service, WebFormRequest $request): JsonResponse
    {
        $request->performValidation();

        $service->submit($request);

        return response()->json('', 204);
    }
}
