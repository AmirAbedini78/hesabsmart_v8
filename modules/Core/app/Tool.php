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

namespace Modules\Core;

use JsonSerializable;

class Tool implements JsonSerializable
{
    protected ?string $description = null;

    /**
     * Initialize new Tool instance.
     *
     * @param  callable  $callback
     */
    public function __construct(protected string $name, protected $callback) {}

    /**
     * Create new Tool instance.
     */
    public static function new(string $name, callable $callback)
    {
        return new static($name, $callback);
    }

    /**
     * Set the tool description.
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the tool name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Execute the tool.
     */
    public function execute(): mixed
    {
        return call_user_func($this->callback);
    }

    /**
     * Prepate the tool for JSON.
     */
    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
