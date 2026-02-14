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

namespace Modules\Core\Menu;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MenuManager
{
    /**
     * Hold the main menu items.
     *
     * @var (MenuItem|callable)[]
     */
    protected array $items = [];

    /**
     * Hold the registered menu metrics.
     *
     * @var \Modules\Core\Menu\Metric[]
     */
    protected array $metrics = [];

    /**
     * Register menu item(s).
     *
     * @param  MenuItem|callable(): MenuItem[]|array<int, MenuItem>  $items
     */
    public function register(MenuItem|callable|array $items): static
    {
        foreach (Arr::wrap($items) as $item) {
            $this->item($item);
        }

        return $this;
    }

    /**
     * Register a single menu item.
     *
     * @param  MenuItem|callable(): MenuItem[]  $item
     */
    public function item(MenuItem|callable $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Add a menu item to an existing group by group ID
     *
     * @param  MenuItem|callable(): MenuItem  $item
     */
    public function addToGroup(string $groupId, MenuItem|callable $item): static
    {
        $this->items[] = function () use ($groupId, $item) {
            $resolvedItem = is_callable($item) ? call_user_func($item) : $item;
            $resolvedItem->setAttribute('_groupId', $groupId);

            return $resolvedItem;
        };

        return $this;
    }

    /**
     * Remove a menu item from a group
     */
    public function removeFromGroup(string $groupId, string $itemId): static
    {
        $this->items = array_filter($this->items, function ($item) use ($groupId, $itemId) {
            if (is_callable($item)) {
                $resolved = call_user_func($item);
                if ($resolved instanceof MenuItem) {
                    return ! ($resolved->getAttribute('_groupId') === $groupId && $resolved->id === $itemId);
                }
            }

            return true;
        });

        return $this;
    }

    /**
     * Register new menu metric.
     *
     * @param  Metric|array<Metric>  $metric
     */
    public function metric(Metric|array $metric): static
    {
        $this->metrics = array_merge($this->metrics, Arr::wrap($metric));

        return $this;
    }

    /**
     * Get all of the registered menu metrics.
     *
     * @return Metric[]
     */
    public function metrics()
    {
        return $this->metrics;
    }

    /**
     * Get all registered menu items.
     */
    public function get(): Collection
    {
        $flatItems = (new Collection($this->items))->map(function (MenuItem|callable|array $item) {
            return is_callable($item) ? call_user_func($item) : $item;
        })->flatten(1)->filter();

        // Group items that should be added to existing groups
        $groupedItems = $this->processGroupedItems($flatItems);

        return $groupedItems->map($this->checkQuickCreateProperties(...))->whenNotEmpty(function (Collection $items) {
            return $this->checkPositions($items);
        })->filter->authorizedToSee()->values();
    }

    /**
     * Process items that should be grouped together
     */
    protected function processGroupedItems(Collection $items): Collection
    {
        $groups = [];
        $standaloneItems = [];

        foreach ($items as $item) {
            if ($item instanceof MenuItem) {
                $groupId = $item->getAttribute('_groupId');

                if ($groupId) {
                    // This item should be added to an existing group
                    if (! isset($groups[$groupId])) {
                        $groups[$groupId] = [];
                    }
                    $groups[$groupId][] = $item;
                } else {
                    $standaloneItems[] = $item;
                }
            }
        }

        // Merge grouped items into their parent groups
        foreach ($standaloneItems as $item) {
            if ($item instanceof MenuItem && $item->collapsible && isset($groups[$item->id])) {
                // Add the grouped items as children
                $existingChildren = $item->children;
                $item->children = array_merge($existingChildren, $groups[$item->id]);
            }
        }

        return new Collection($standaloneItems);
    }

    /**
     * Clears all the registered menu items and metrics.
     */
    public function clear(): static
    {
        $this->items = [];
        $this->metrics = [];

        return $this;
    }

    /**
     * Check if order is set and sort the items.
     */
    protected function checkPositions(Collection $items): Collection
    {
        /**
         * If there is no position set, add the index + 5
         */
        $items->each(function (MenuItem $item, int $index) {
            if (! $item->position) {
                $item->position($index + 10);
            }
        });

        /**
         * Sort the items with the actual order
         */
        return $this->sort($items);
    }

    /**
     * Check quick create properties and add default props.
     */
    protected function checkQuickCreateProperties(MenuItem $item): MenuItem
    {
        if ($item->inQuickCreate) {
            if (! $item->quickCreateRoute) {
                $item->quickCreateRoute(rtrim($item->route, '/').'/'.'create');
            }

            if (! $item->quickCreateName) {
                $item->quickCreateName($item->singularName ?? $item->name);
            }
        }

        return $item;
    }

    /**
     * Sort the items.
     */
    protected function sort(Collection $items): Collection
    {
        return $items->sort(function ($a, $b) {
            if ($a->position == $b->position) {
                return 0;
            }

            return ($a->position < $b->position) ? -1 : 1;
        })->values();
    }
}
