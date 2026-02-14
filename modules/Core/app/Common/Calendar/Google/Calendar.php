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

namespace Modules\Core\Common\Calendar\Google;

use Modules\Core\Common\Calendar\AbstractCalendar;

class Calendar extends AbstractCalendar
{
    /**
     * Get the calendar ID.
     */
    public function getId(): string
    {
        return $this->getEntity()->getId();
    }

    /**
     * Get the calendar title.
     */
    public function getTitle(): string
    {
        return $this->getEntity()->getSummary();
    }

    /**
     * Check whether the calendar is default.
     */
    public function isDefault(): bool
    {
        return $this->getEntity()->getPrimary() ?: false;
    }
}
