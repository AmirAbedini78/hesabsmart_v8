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

use Modules\Core\Contracts\Fields\Customfieldable;

class Radio extends Optionable implements Customfieldable
{
    /**
     * Field component.
     */
    protected static $component = 'radio-field';

    /**
     * Indicates that the radio field will be inline
     */
    public function inline(): static
    {
        $this->withMeta(['inline' => true]);

        return $this;
    }

    /**
     * Create the custom field value column in database.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     */
    public static function createValueColumn($table, string $fieldId): void
    {
        $table->unsignedBigInteger($fieldId)->nullable();
        $table->foreign($fieldId)
            ->references('id')
            ->on('custom_field_options');
    }
}
