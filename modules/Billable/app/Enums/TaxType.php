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

namespace Modules\Billable\Enums;

use Modules\Core\Support\InteractsWithEnums;

enum TaxType: int
{
    use InteractsWithEnums;

    case exclusive = 1;
    case inclusive = 2;
    case no_tax = 3;
}
