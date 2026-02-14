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

namespace Modules\Core\Contracts;

/**
 * Currently only the methods defined are used.
 */
interface Metable
{
    /**
     * Add or update the value of the `Meta` at a given key.
     */
    public function setMeta(string $key, mixed $value): void;

    /**
     * Check if a `Meta` has been set at a given key.
     */
    public function hasMeta(string $key): bool;

    /**
     * Delete the `Meta` at a given key.
     */
    public function removeMeta(string $key): void;

    /**
     * Retrieve the value of the `Meta` at a given key.
     */
    public function getMeta(string $key, mixed $default = null): mixed;
}
