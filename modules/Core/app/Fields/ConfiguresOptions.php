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

use UnexpectedValueException;

trait ConfiguresOptions
{
    /**
     * Add an action when an option is clicked on detail and index view.
     */
    public function onOptionClick(string $action, array $data): static
    {
        if (! in_array($action, ['float', 'redirect'])) {
            throw new UnexpectedValueException('The action must be either "float" or "redirect".');
        }

        $this->withMeta(['onOptionClick' => array_merge($data, ['action' => $action])]);

        return $this;
    }

    /**
     * Set that the options should be displayed as pills on detail and index view.
     */
    public function displayAsBadges(): static
    {
        $this->withMeta(['displayAsBadges' => true]);

        return $this;
    }

    /**
     * Set that the options should be displayed in new line on detail and index view.
     */
    public function eachOnNewLine(): static
    {
        $this->withMeta(['eachOnNewLine' => true]);

        return $this;
    }
}
