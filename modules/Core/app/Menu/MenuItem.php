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

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;
use Modules\Core\Support\Authorizeable;
use Modules\Core\Support\Makeable;

class MenuItem implements Arrayable, JsonSerializable
{
    use Authorizeable, Makeable;

    /**
     * The menu item id.
     */
    public string $id;

    /**
     * Singular name e.q. Contact, translates to Create "Contact".
     */
    public ?string $singularName = null;

    /**
     * Does this item should be shown on quick create section.
     */
    public bool $inQuickCreate = false;

    /**
     * Route for quick create.
     */
    public ?string $quickCreateRoute = null;

    /**
     * Custom quick create name.
     */
    public ?string $quickCreateName = null;

    /**
     * Badge for the sidebar item.
     */
    public null|string|int|float|Closure $badge = null;

    /**
     * Badge color variant.
     */
    public string $badgeVariant = 'warning';

    /**
     * Menu item icon.
     */
    public ?string $icon = null;

    /**
     * Menu item position.
     */
    public ?string $position = null;

    /**
     * Badge color variant.
     */
    public ?string $keyboardShortcutChar = null;

    /**
     * Children menu items for collapsible groups.
     */
    public array $children = [];

    /**
     * Whether this item is collapsible (has children).
     */
    public bool $collapsible = false;

    /**
     * Default collapsed state for collapsible items.
     */
    public bool $defaultCollapsed = false;

    /**
     * Additional attributes for the menu item.
     */
    protected array $attributes = [];

    /**
     * Initialize new MenuItem instance.
     */
    public function __construct(public string $name, public string $route)
    {
        // For placeholder routes like '#', use the name to generate ID
        $this->id = $route === '#' ? Str::slug($name) : Str::slug($route);
    }

    /**
     * Set the menu item ID.
     */
    public function id(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set menu item icon.
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set badge for the menu item.
     */
    public function badge(string|int|float|Closure $value): static
    {
        $this->badge = $value;

        return $this;
    }

    /**
     * Set badge variant.
     */
    public function badgeVariant(string $value): static
    {
        $this->badgeVariant = $value;

        return $this;
    }

    /**
     * Get badge for the menu item.
     */
    public function getBadge(): mixed
    {
        if ($this->badge instanceof \Closure) {
            return ($this->badge)();
        }

        return $this->badge;
    }

    /**
     * Set menu item position.
     */
    public function position(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Set menu item singular name.
     */
    public function singularName(string $singular): static
    {
        $this->singularName = $singular;

        return $this;
    }

    /**
     * Whether this item should be also included in the quick create section.
     */
    public function inQuickCreate(bool $bool = true): static
    {
        $this->inQuickCreate = $bool;

        return $this;
    }

    /**
     * Set the keyboard shortcut character.
     */
    public function keyboardShortcutChar(string $char): static
    {
        $this->keyboardShortcutChar = $char;

        return $this;
    }

    /**
     * Custom quick create route.
     * Default route is e.q. contacts/create
     */
    public function quickCreateRoute(string $route): static
    {
        $this->quickCreateRoute = $route;

        return $this;
    }

    /**
     * Custom quick create name.
     */
    public function quickCreateName(string $name): static
    {
        $this->quickCreateName = $name;

        return $this;
    }

    /**
     * Add child menu items (makes this item collapsible).
     */
    public function children(array $children): static
    {
        $this->children = $children;
        $this->collapsible = true;

        return $this;
    }

    /**
     * Set default collapsed state for collapsible items.
     */
    public function defaultCollapsed(bool $collapsed = true): static
    {
        $this->defaultCollapsed = $collapsed;

        return $this;
    }

    /**
     * Static method to create a collapsible menu group.
     */
    public static function group(string $name, array $children, ?string $icon = null): static
    {
        return static::make($name, '#')
            ->children($children)
            ->icon($icon);
    }

    /**
     * Set an attribute for the menu item.
     */
    public function setAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get an attribute from the menu item.
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'singularName' => $this->singularName,
            'route' => $this->route,
            'icon' => $this->icon,
            'inQuickCreate' => $this->inQuickCreate,
            'quickCreateRoute' => $this->quickCreateRoute,
            'quickCreateName' => $this->quickCreateName,
            'position' => $this->position,
            'badge' => $this->getBadge(),
            'badgeVariant' => $this->badgeVariant,
            'keyboardShortcutChar' => $this->keyboardShortcutChar,
            'children' => array_map(fn ($child) => $child instanceof self ? $child->toArray() : $child, $this->children),
            'collapsible' => $this->collapsible,
            'defaultCollapsed' => $this->defaultCollapsed,
        ];
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
