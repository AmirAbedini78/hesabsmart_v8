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

namespace Modules\Core\Actions;

class ActionFields
{
    /**
     * Create new instance of action request fields
     */
    public function __construct(protected array $fields) {}

    /**
     * Get all of the available fields.
     */
    public function all(): array
    {
        return $this->fields;
    }

    /**
     * Get field
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->fields[$name] ?? null;
    }
}
