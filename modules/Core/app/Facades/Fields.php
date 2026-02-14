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
use Modules\Core\Fields\FieldsManager;

/**
 * @method static static group(string $group, mixed $provider)
 * @method static static add(string $group, mixed $provider)
 * @method static static replace(string $group, mixed $provider)
 * @method static bool has(string $group)
 * @method static \Modules\Core\Fields\FieldsCollection get(string $group, string $view)
 * @method static \Modules\Core\Fields\FieldsCollection getForSettings(string $group, string $view)
 * @method static \Modules\Core\Fields\FieldsCollection inGroup(string $group, ?string $view = null)
 * @method static void customize(mixed $data, string $group, string $view)
 * @method static array customized(string $group, string $view, ?string $attribute = null)
 * @method static void flushLoadedCache()
 * @method static void flushRegisteredCache()
 * @method static \Illuminate\Support\Collection customFieldable()
 * @method static array getOptionableCustomFieldsTypes()
 * @method static array getNonOptionableCustomFieldsTypes()
 * @method static array customFieldsTypes()
 * @method static \Modules\Core\Fields\Field applyCustomizedAttributes(\Modules\Core\Fields\Field $field, string $group, ?string $view)
 *
 * @see \Modules\Core\Fields\FieldsManager
 */
class Fields extends Facade
{
    /**
     * The index view name
     */
    const INDEX_VIEW = 'index';

    /**
     * The create view name
     */
    const CREATE_VIEW = 'create';

    /**
     * The detail view name
     */
    const DETAIL_VIEW = 'detail';

    /**
     * The update view name
     */
    const UPDATE_VIEW = 'update';

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return FieldsManager::class;
    }
}
