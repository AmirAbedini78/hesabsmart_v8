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
use Modules\Core\MailableTemplate\MailableTemplatesManager;

/**
 * @method static static register(string|array $mailable)
 * @method static array get()
 * @method static bool shouldSeed()
 * @method static static seed()
 * @method static static seedForLocale(string $locale, string $mailable = null)
 *
 * @see \Modules\Core\MailableTemplate\MailableTemplatesManager
 */
class MailableTemplates extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return MailableTemplatesManager::class;
    }
}
