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

class Url extends Field implements Customfieldable, UniqueableCustomfield
{
    /**
     * Field component.
     */
    protected static $component = 'url-field';

    /**
     * Initialize new Url instance.
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->provideSampleValueUsing(fn () => config('app.url'));
    }

    /**
     * Include "https" in front of the URL.
     */
    public function https(bool $value = true)
    {
        $this->withMeta(['https' => $value]);

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
}
