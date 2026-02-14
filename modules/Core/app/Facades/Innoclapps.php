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

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Modules\Core\Resource\Resource resourceByName(string $name)
 * @method static \Modules\Core\Resource\Resource resourceByModel(string|Illuminate\Database\Eloquent\Model $model)
 * @method static void whenReadyForServing(callable $callback)
 *
 * @see \Modules\Core\Application
 * */
class Innoclapps extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'core';
    }

    /**
     * @deprecated
     */
    public static function booting(callable $callback)
    {
        app()->booting($callback);
    }

    /**
     * @deprecated
     */
    public static function booted(callable $callback)
    {
        app()->booted($callback);
    }
}
