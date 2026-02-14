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

use Illuminate\Validation\Rule;
use Modules\Core\Contracts\Fields\Dateable;
use Modules\Core\Contracts\Resources\Exportable;
use Modules\Core\Fields\Field;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends ApiController
{
    /**
     * Export resource data
     */
    public function handle(ResourceRequest $request): BinaryFileResponse
    {
        abort_unless($request->resource() instanceof Exportable, 404);

        $this->authorize('export', $request->resource()::$model);

        $availableFields = $request->resource()->fieldsForExport();

        $request->validate([
            'date_range_field' => [
                'sometimes',
                'nullable',
                Rule::in($availableFields->filter(
                    fn (Field $field) => $field instanceof Dateable)->pluck('attribute')
                )],
        ]);

        $filteredFields = $availableFields->when(is_array($request->input('fields')), function ($fields) use ($request) {
            return $fields->filter(fn (Field $field) => $field->isPrimary() || in_array($field->attribute, $request->fields));
        });

        $query = $request->resource()->exportQuery($request, $filteredFields);

        return $request->resource()
            ->exportable($query)
            ->setUser($request->user())
            ->setFields($filteredFields)
            ->download($request->type);
    }
}
