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

namespace Modules\Core\Support;

use Illuminate\Support\Collection;

trait InteractsWithEnums
{
    /**
     * Find enum by given name.
     */
    public static function find(string $name): ?static
    {
        return array_values(array_filter(static::cases(), function ($status) use ($name) {
            return $status->name == $name;
        }))[0] ?? null;
    }

    /**
     * Get a random enum instance.
     */
    public static function random(): self
    {
        return static::find(static::names()[array_rand(static::names())]);
    }

    /**
     * Get all the enum names.
     */
    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }

    /**
     * Get all the enum values.
     */
    public static function values(): array
    {
        return array_column(static::cases(), 'value');
    }

    /**
     * Get Collection of enums.
     */
    public static function collection(): Collection
    {
        return collect(static::cases());
    }
}
