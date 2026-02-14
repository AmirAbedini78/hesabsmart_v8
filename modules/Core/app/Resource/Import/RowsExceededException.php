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

namespace Modules\Core\Resource\Import;

use Exception;

class RowsExceededException extends Exception
{
    /**
     * Create new RowsExceededException instance.
     */
    public function __construct(int $totalRows)
    {
        parent::__construct(
            'The maximum rows ('.$totalRows.') allowed in import file may have exceeded. Consider splitting the import data in multiple files.'
        );
    }
}
