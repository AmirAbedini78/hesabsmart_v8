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

namespace Modules\Core\Resource;

use Illuminate\Support\Collection;
use JsonSerializable;
use Modules\Core\Contracts\Resources\Resourceable;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;

class GlobalSearch implements JsonSerializable
{
    /**
     * Initialize global search for the given resources.
     *
     * @param  \Modules\Core\Resource\Resource[]  $resources
     */
    public function __construct(protected ResourceRequest $request, protected array $resources) {}

    /**
     * Get the search result.
     */
    public function get(): Collection
    {
        $result = new Collection([]);

        foreach ($this->resources as $resource) {
            if (count($resource->globalSearchColumns()) > 0) {
                $result->push([
                    'title' => $resource->label(),
                    'resource_name' => $resource::name(),
                    'icon' => $resource::$icon,
                    'action' => $resource::$globalSearchAction,
                    'data' => $resource->globalSearchQuery($this->request)
                        ->take($resource::$globalSearchResultsLimit)
                        ->get()
                        ->map(fn (Model&Resourceable $model) => $this->data($model, $resource)),
                ]);
            }
        }

        return $result;
    }

    /**
     * Get the model data for the response.
     */
    protected function data(Model&Resourceable $model, Resource $resource): array
    {
        return [
            'id' => $model->getKey(),
            'path' => $resource->globalSearchResultViewUrl($model),
            'display_name' => $resource->globalSearchTitle($model),
            'created_at' => $model->created_at,
            'resourceName' => $resource->name(),
        ];
    }

    /**
     * Serialize GlobalSearch class.
     */
    public function jsonSerialize(): array
    {
        return $this->get()->all();
    }
}
