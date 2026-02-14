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

namespace Modules\Core\Settings;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SettingsMenu
{
    /**
     * Hold the main menu items.
     *
     * @var (SettingsMenuItem|callable)[]
     */
    protected array $items = [];

    /**
     * All of the resolved items.
     */
    protected ?Collection $resolved = null;

    /**
     * Additional added items.
     */
    protected array $added = [];

    /**
     * Register new settings menu item.
     *
     * @param  SettingsMenuItem|callable(): SettingsMenuItem[]|array<int, SettingsMenuItem>  $items
     */
    public function register(SettingsMenuItem|callable|array $items): void
    {
        foreach (Arr::wrap($items) as $item) {
            $this->items[] = $item;
        }
    }

    /**
     * Register a single settings menu item.
     *
     * @param  SettingsMenuItem|callable(): SettingsMenuItem[]  $item
     */
    public function item(SettingsMenuItem|callable $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Add children menu item to existing item.
     *
     * @param  SettingsMenuItem|callable(): SettingsMenuItem[]  $item
     */
    public function add(string $parentId, SettingsMenuItem|callable|array $item)
    {
        if (! array_key_exists($parentId, $this->added)) {
            $this->added[$parentId] = [];
        }

        $this->added[$parentId][] = $item;
    }

    /**
     * Find menu item by the given id.
     */
    public function find(string $id): ?SettingsMenuItem
    {
        return $this->all()->first(fn (SettingsMenuItem $item) => $item->getId() === $id);
    }

    /**
     * Get all of the registered settings menu items.
     */
    public function all(): Collection
    {
        if (isset($this->resolved)) {
            return $this->resolved;
        }

        $resolved = collect($this->items)
            ->map(function (SettingsMenuItem|callable|array $item) {
                return is_callable($item) ? call_user_func($item) : $item;
            })
            ->flatten(1)
            ->filter()
            ->each(function (SettingsMenuItem $item) {
                if (isset($this->added[$item->getId()])) {
                    $item->withChildren($this->added[$item->getId()]);
                }
            })->sortBy('order')->values();

        $resolved = apply_filters('settings.menu.all', $resolved);

        $this->resolved = $resolved->filter(fn ($item) => $item->authorizedToSee());

        return $this->resolved;
    }
}
