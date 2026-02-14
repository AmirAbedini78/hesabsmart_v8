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

use Illuminate\Support\Arr;
use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Facades\Timezone as Facade;

class Timezone extends Field implements Customfieldable
{
    /**
     * Field component.
     */
    protected static $component = 'timezone-field';

    /**
     * Initialize Timezone field
     *
     * @param  string  $attribute
     * @param  string|null  $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label ?? __('core::app.timezone'));

        $this->rules(['nullable', 'timezone:all'])
            ->provideSampleValueUsing(fn () => Arr::random(tz()->all()));
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
     * Provide the options intended for Zapier
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'timezones' => Facade::toArray(),
        ]);
    }
}
