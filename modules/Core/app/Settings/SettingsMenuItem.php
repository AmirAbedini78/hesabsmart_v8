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

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;
use Modules\Core\Support\Makeable;
use Modules\Core\Support\Authorizeable;

class SettingsMenuItem implements Arrayable, JsonSerializable
{
    use Authorizeable, Makeable;

    public ?int $order = null;

    protected array $children = [];

    protected ?string $path = null;

    protected ?string $icon = null;

    /**
     * Create new SettingsMenuItem instance.
     */
    public function __construct(protected string $id, protected string $title, array|string $children = [])
    {
        if (is_string($children)) {
            // Backward compatibilities, it should not be used.
            $this->icon($children);
        } else {
            $this->children = $children;
        }
    }

    /**
     * Set the item path.
     */
    public function path(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set the item route.
     *
     * @deprecated
     */
    public function route(string $icon): static
    {
        $this->path(Str::replaceStart('/settings', '', $icon));

        return $this;
    }

    /**
     * Set the item icon.
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the item order.
     */
    public function order(int $order): static
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Register child menu item.
     *
     * @deprecated
     */
    public function withChild(self|callable|array $item): static
    {
        $this->withChildren($item);

        return $this;
    }

    /**
     * Register menu items.
     */
    public function withChildren(self|callable|array $item): static
    {
        $this->children[] = $item;

        return $this;
    }

    /**
     * Set the item child items.
     */
    public function setChildren(array $items): static
    {
        $this->children = $items;

        return $this;
    }

    /**
     * Get the item child items.
     */
    public function getChildren(): array
    {
        $toItemCallback = function (SettingsMenuItem|callable|array $item) {
            return is_callable($item) ? call_user_func($item) : $item;
        };

        return collect($this->children)
            ->map($toItemCallback)
            ->flatten(1)
            ->map($toItemCallback)
            ->flatten(1)
            ->filter(fn ($item) => $item->authorizedToSee())
            ->sort(function (SettingsMenuItem $a, SettingsMenuItem $b) {
                // We will treat null as maximum integer to sort it last
                $orderA = $a->order ?? PHP_INT_MAX;
                $orderB = $b->order ?? PHP_INT_MAX;

                return $orderA <=> $orderB;
            })->values()->all();
    }

    /**
     * Get the menu item unique identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the menu item unique identifier.
     *
     * @deprecated
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * toArray
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'path' => $this->path ? '/settings/'.Str::replaceStart('/', '', $this->path) : null,
            'icon' => $this->icon,
            'children' => $this->getChildren(),
            'order' => $this->order,
        ];
    }

    /**
     * Prepare the item for JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
