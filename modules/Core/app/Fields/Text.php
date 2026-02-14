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

class Text extends Field implements Customfieldable, UniqueableCustomfield
{
    use ChecksForDuplicates;

    /**
     * Input type
     */
    public string $inputType = 'text';

    /**
     * Field component.
     */
    protected static $component = 'text-field';

    /**
     * Specify type attribute for the text field
     */
    public function inputType(string $type): static
    {
        $this->inputType = $type;

        return $this;
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

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'inputType' => $this->inputType,
        ]);
    }
}
