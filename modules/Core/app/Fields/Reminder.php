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

namespace Modules\Core\Fields;

class Reminder extends Field
{
    /**
     * Field component.
     */
    protected static $component = 'reminder-field';

    /**
     * Indicates whether to allow the user to cancel the reminder
     */
    public function cancelable(): static
    {
        $this->rules('nullable');

        $this->withMeta([__FUNCTION__ => true]);

        return $this;
    }
}
