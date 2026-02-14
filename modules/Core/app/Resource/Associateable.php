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

use Illuminate\Support\Facades\Cache;
use Modules\Core\Facades\Innoclapps;

trait Associateable
{
    /**
     * Provide the resource relationship name when it's associated.
     */
    public function associateableName(): ?string
    {
        return null;
    }

    /**
     * Determine whether the resource is associateable.
     */
    public function isAssociateable(): bool
    {
        return ! is_null($this->associateableName());
    }

    /**
     * Check whether the given resource can be associated to the current resource.
     */
    public function canBeAssociatedTo(Resource|string $resource): bool
    {
        $name = $resource instanceof Resource ? $resource->name() : $resource;

        return (bool) $this->associateableResources()->first(
            fn (Resource $resource) => $resource->name() == $name
        );
    }

    /**
     * Get the resource available associateable resources.
     *
     * @return \Illuminate\Support\Collection<string, \Modules\Core\Resource\Resource>
     */
    public function associateableResources()
    {
        return Cache::store('array')->rememberForever($this->name().'-associateables', function () {
            return Innoclapps::registeredResources()
                ->filter(fn (Resource $resource) => $resource->isAssociateable())
                ->filter(fn (Resource $resource) => $this->newModel()->isRelation($resource->associateableName()))
                ->values()
                ->mapWithKeys(fn (Resource $resource) => [$resource->associateableName() => $resource]);
        });
    }

    /**
     * Get the resource associateable relations.
     *
     * @return string[]
     */
    public function associateableRelations(): array
    {
        return $this->associateableResources()->keys()->all();
    }
}
