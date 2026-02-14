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

namespace Modules\Core\Zapier;

use Exception;

class ActionNotSupportedException extends Exception
{
    /**
     * Initialize ActionNotSupportedException
     */
    public function __construct($action, $code = 0, ?Exception $previous = null)
    {
        parent::__construct("$action is not supported.", $code, $previous);
    }
}
