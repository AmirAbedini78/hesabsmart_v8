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

use JsonSerializable;
use Modules\Core\Support\Makeable;

class Panel implements JsonSerializable
{
    use Makeable;

    /**
     * Indicates whether the panel is resizeable.
     */
    public bool $resizeable = false;

    /**
     * Indicates whether the panel is enabled.
     */
    public bool $enabled = true;

    /**
     * Panel order.
     */
    public ?int $order = null;

    /**
     * Panel heading.
     */
    public ?string $heading = null;

    /**
     * Create new Panel instance.
     */
    public function __construct(public string $id, public string $component) {}

    /**
     * Mark the panel as resizeable.
     */
    public function resizeable(): static
    {
        $this->resizeable = true;

        return $this;
    }

    /**
     * Set the panel heading.
     */
    public function heading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'order' => $this->order,
            'component' => $this->component,
            'enabled' => $this->enabled,
            'heading' => $this->heading,
            'resizeable' => $this->resizeable,
            'height' => settings($this->id.'_panel_height'),
        ];
    }
}
