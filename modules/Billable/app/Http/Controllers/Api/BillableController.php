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

namespace Modules\Billable\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Modules\Billable\Contracts\BillableResource as BillableResourceContract;
use Modules\Billable\Enums\TaxType;
use Modules\Billable\Http\Resources\BillableResource;
use Modules\Billable\Services\BillableService;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Rules\StringRule;

class BillableController extends ApiController
{
    /**
     * Get the resource billable.
     */
    public function show(ResourceRequest $request): JsonResponse
    {
        abort_unless($request->resource() instanceof BillableResourceContract, 404);

        $this->authorize('view', $request->record());

        $billable = isset($request->record()->billable)
            ? new BillableResource($request->record()->billable->load('products'))
            : []
        ;

        return $this->response($billable);
    }

    /**
     * Handle the resource billable request.
     */
    public function save(ResourceRequest $request, BillableService $service): JsonResponse
    {
        abort_unless($request->resource() instanceof BillableResourceContract, 404);

        $this->authorize('update', $request->record());

        $request->validate([
            'tax_type' => ['nullable', 'string', Rule::in(TaxType::names())],
            'description' => 'nullable|string', // todo, is it used?
            'products.*.name' => ['sometimes', 'required', StringRule::make()],
            'products.*.discount_type' => 'nullable|string|in:fixed,percent',
            'products.*.display_order' => 'integer',
            'products.*.qty' => 'nullable|regex:/^[0-9]\d*(\.\d{0,2})?$/',
            'products.*.unit' => ['nullable', StringRule::make()],
            'products.*.tax_label' => ['nullable', StringRule::make()],
            'products.*.tax_rate' => ['nullable', 'numeric', 'decimal:0,3', 'min:0'],
            'products.*.product_id' => 'nullable|integer',
        ], [], ['products.*.name' => __('billable::product.product')]);

        $billable = $service->save($request->all(), $request->record())->load('products');

        return $this->response(new BillableResource($billable));
    }
}
