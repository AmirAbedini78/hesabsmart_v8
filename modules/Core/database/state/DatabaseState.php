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

namespace Modules\Core\Database\State;

use Illuminate\Database\Eloquent\Model;

class DatabaseState
{
    protected static array $seeders = [];

    public static function register(string|array $class): void
    {
        static::$seeders = array_unique(array_merge(static::$seeders, (array) $class));
    }

    public static function seed(): void
    {
        collect(static::$seeders)->map(fn (string $class) => new $class)->each(function (object $instance) {
            Model::unguarded(function () use ($instance) {
                $instance->__invoke();
            });
        });
    }
}
