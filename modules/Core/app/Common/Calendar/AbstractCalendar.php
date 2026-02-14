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

namespace Modules\Core\Common\Calendar;

use Modules\Core\Contracts\Calendar\Calendar as CalendarInterface;
use Modules\Core\Support\AbstractMask;

abstract class AbstractCalendar extends AbstractMask implements CalendarInterface
{
    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * toArray
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'is_default' => $this->isDefault(),
        ];
    }
}
