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

namespace Modules\Contacts\Enums;

use Modules\Core\Support\InteractsWithEnums;

enum PhoneType: int
{
    use InteractsWithEnums;

    case mobile = 1;
    case work = 2;
    case other = 3;
}
