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

namespace Modules\Core\Contracts\Workflow;

interface FieldChangeTrigger
{
    /**
     * The field to track changes on
     */
    public static function field(): string;

    /**
     * Provide the change field
     *
     * @return \Modules\Core\Fields\Field
     */
    public static function changeField();
}
