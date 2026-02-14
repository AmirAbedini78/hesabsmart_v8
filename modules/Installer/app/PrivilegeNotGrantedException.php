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

namespace Modules\Installer;

use Exception;

class PrivilegeNotGrantedException extends Exception
{
    /**
     * Get the privilege name that was denied.
     */
    public function getPriviligeName(): string
    {
        $re = '/[0-9]+\s([A-Z]+)+\scommand denied/m';

        preg_match($re, $this->getMessage(), $matches);

        return $matches[1] ?? '';
    }
}
