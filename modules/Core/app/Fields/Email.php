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
use Modules\Core\Contracts\Fields\UniqueableCustomfield;

class Email extends Field implements Customfieldable, UniqueableCustomfield
{
    use ChecksForDuplicates;

    /**
     * Field component.
     */
    protected static $component = 'email-field';

    /**
     * Initialize new Email instance class
     *
     * @param  string  $attribute  field attribute
     * @param  string|null  $label  field label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->rules(['nullable', 'email'])->provideSampleValueUsing(fn () => uniqid().'@example.com');
    }

    /**
     * Create the custom field value column in database.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     */
    public static function createValueColumn($table, string $fieldId): void
    {
        $table->string($fieldId)->nullable();
    }
}
