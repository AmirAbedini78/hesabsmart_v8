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
use Modules\Core\Common\Changelog\Logging as BaseLogging;

/**
 * @method static static disable()
 * @method static static enable()
 * @method static void disabled(\Closure $callback)
 * @method static \Modules\Core\Models\Changelog onModel(\Modules\Core\Models\Model $model, array $attributes)
 *
 * @see \Modules\Core\Common\Changelog\Logging
 */
class ChangeLogger extends Facade
{
    /**
     * Indicates the model log name
     */
    const MODEL_LOG_NAME = 'model';

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return BaseLogging::class;
    }
}
