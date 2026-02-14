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

namespace Modules\Core\Pages;

use Illuminate\Support\Collection;

class StandardDetailPage extends Page
{
    /**
     * Registered panels.
     *
     * @var (Panel|callable)[]
     */
    protected array $panels = [];

    /**
     * Registered tabs.
     *
     * @var (Tab|callable)[]
     */
    protected array $tabs = [];

    /**
     * Provide a callback to register panels.
     */
    public function panels(callable $callback): static
    {
        $this->panels[] = $callback;

        return $this;
    }

    /**
     * Register new panel.
     */
    public function panel(callable|Panel $panel): static
    {
        $this->panels[] = $panel;

        return $this;
    }

    /**
     * Get all of the registered panels.
     */
    public function getPanels(): Collection
    {
        return (new Collection($this->panels))->map(function (Panel|callable|array $item) {
            return is_callable($item) ? call_user_func($item) : $item;
        })->flatten(1)->filter();
    }

    /**
     * Merge the panels options with the given ones section
     */
    protected function mergePanels(array $settings): array
    {
        $settings = collect($settings);

        return $this->getPanels()->map(function (Panel $panel) use ($settings) {
            if ($option = $settings->firstWhere('id', $panel->id)) {
                $panel->enabled = $option['enabled'];
                $panel->order = $option['order'];
            }

            return $panel;
        })->sortBy('order')->values()->all();
    }

    /**
     * Provide a callback to register tabs.
     */
    public function tabs(callable $callback): static
    {
        $this->tabs[] = $callback;

        return $this;
    }

    /**
     * Get all of the registered tabs.
     */
    public function getTabs(): Collection
    {
        return (new Collection($this->tabs))->map(function (Tab|callable|array $item) {
            return is_callable($item) ? call_user_func($item) : $item;
        })->flatten(1)->filter();
    }

    /**
     * Register new tab.
     */
    public function tab(callable|Tab $tab): static
    {
        $this->tabs[] = $tab;

        return $this;
    }

    /**
     * Get the ordered tabs.
     */
    public function orderedTabs(): array
    {
        return collect($this->getTabs())->sortBy('displayOrder')->values()->all();
    }

    /**
     * Get an array represenation of the component data.
     */
    public function toArray(): array
    {
        return [
            'tabs' => $this->orderedTabs(),
            'panels' => $this->mergePanels(
                settings($this->id.'_panels') ?: []
            ),
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
