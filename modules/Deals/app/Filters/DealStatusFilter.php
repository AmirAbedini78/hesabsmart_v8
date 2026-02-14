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

namespace Modules\Deals\Filters;

use Modules\Core\Filters\Select;
use Modules\Deals\Enums\DealStatus as StatusEnum;

class DealStatusFilter extends Select
{
    /**
     * Initialize Source class
     */
    public function __construct()
    {
        parent::__construct('status', __('deals::deal.status.status'));

        $this->options(collect(StatusEnum::cases())->mapWithKeys(function (StatusEnum $status) {
            return [$status->name => $status->label()];
        })->all());
    }

    /**
     * Prepare the query value.
     */
    public function prepareValue(string $value): ?StatusEnum
    {
        return StatusEnum::find($value);
    }
}
