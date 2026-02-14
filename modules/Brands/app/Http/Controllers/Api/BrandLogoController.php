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
use Modules\Brands\Models\Brand;
use Modules\Brands\Services\BrandLogoService;
use Modules\Core\Http\Controllers\ApiController;

class BrandLogoController extends ApiController
{
    /**
     * Upload the given brand logo.
     */
    public function store(Brand $brand, string $type, Request $request, BrandLogoService $service): JsonResponse
    {
        $this->authorize('update', $brand);

        $request->validate([
            'logo_'.$type => 'required|image|max:1024',
        ]);

        $brand = $service->store($request->file('logo_'.$type), $brand, $type);

        return $this->response([
            'path' => $brand->{'logo_'.$type},
            'url' => $brand->{'logo'.ucfirst($type).'Url'},
        ]);
    }

    /**
     * Remove the specified brand logo.
     */
    public function delete(Brand $brand, string $type, BrandLogoService $service): void
    {
        $this->authorize('update', $brand);

        $service->delete($brand, $type);

        $brand->{'logo_'.$type} = null;
        $brand->save();
    }
}
