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

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Resource\PlaceholdersGroup;
use Modules\Core\Resource\ResourcePlaceholders;

class PlaceholdersController extends ApiController
{
    /**
     * Retrieve placeholders via fields.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->response(ResourcePlaceholders::createGroupsFromResources(
            $request->input('resources', [])
        ));
    }

    /**
     * Parse placeholders via input fields.
     */
    public function parseViaInputFields(Request $request): JsonResponse
    {
        $resources = $request->input('resources', []);

        return $this->response(
            $this->placeholders($resources, $request)->parseWhenViaInputFields($request->content)
        );
    }

    /**
     * Parse placeholders via interpolation.
     */
    public function parseViaInterpolation(Request $request): JsonResponse
    {
        $resources = $request->input('resources', []);

        return $this->response(
            $this->placeholders($resources, $request)->render($request->content)
        );
    }

    /**
     * Create new Placeholders instance from the given resources.
     */
    protected function placeholders(array $resources, Request $request): ResourcePlaceholders
    {
        $groups = [];

        foreach ($resources as $resource) {
            $instance = Innoclapps::resourceByName($resource['name']);

            if ($instance) {
                $record = $instance->displayQuery()->find($resource['id']);

                if ($request->user()->can('view', $record)) {
                    $groups[$resource['name']] = new PlaceholdersGroup($instance, $record);
                }
            }
        }

        return new ResourcePlaceholders(array_values($groups));
    }
}
