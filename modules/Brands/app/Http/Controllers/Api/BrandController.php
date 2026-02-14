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

namespace Modules\Brands\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Brands\Http\Requests\BrandRequest;
use Modules\Brands\Http\Resources\BrandResource;
use Modules\Brands\Models\Brand;
use Modules\Brands\Services\BrandService;
use Modules\Core\Http\Controllers\ApiController;

class BrandController extends ApiController
{
    /**
     * Display a listing of the company brands.
     */
    public function index(): JsonResponse
    {
        $brands = Brand::visible()
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return $this->response(BrandResource::collection($brands));
    }

    /**
     * Display the specified company brand.
     */
    public function show(Brand $brand, Request $request): JsonResponse
    {
        $this->authorize('view', $brand);

        $brand->loadMissing($request->getWith());

        return $this->response(new BrandResource($brand));
    }

    /**
     * Store a newly created company brand in storage.
     */
    public function store(BrandRequest $request, BrandService $service): JsonResponse
    {
        $this->authorize('create', Brand::class);

        $brand = $service->create($request->input());

        return $this->response(
            new BrandResource($brand),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Update the specified company brand in storage.
     */
    public function update(Brand $brand, BrandRequest $request, BrandService $service): JsonResponse
    {
        $this->authorize('update', $brand);

        $brand = $service->update($request->input(), $brand);

        $brand->loadMissing($request->getWith());

        return $this->response(
            new BrandResource($brand)
        );
    }

    /**
     * Remove the specified company brand from storage.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        $this->authorize('delete', $brand);

        $brand->delete();

        return $this->response('', JsonResponse::HTTP_NO_CONTENT);
    }
}
