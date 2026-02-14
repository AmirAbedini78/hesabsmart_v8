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

class RowSkippedException extends Exception
{
    /**
     * @var \Modules\Core\Resource\Import\Failure[]
     */
    protected array $failures;

    /**
     * Create new RowSkippedException instance.
     */
    public function __construct(Failure ...$failures)
    {
        $this->failures = $failures;

        parent::__construct();
    }

    /**
     * @return \Modules\Core\Resource\Import\Failure[]
     */
    public function failures(): array
    {
        return $this->failures;
    }
}
