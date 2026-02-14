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

namespace Modules\Documents\Enums;

enum DocumentViewType: string
{
    case NAV_TOP = 'nav-top';
    case NAV_LEFT = 'nav-left';
    case NAV_LEFT_FULL_WIDTH = 'nav-left-full-width';
}
