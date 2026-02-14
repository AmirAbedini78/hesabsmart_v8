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

use Modules\Core\Table\BelongsToManyColumn;

/**
 * Currently used only for INDEX, not working on forms or other views.
 */
class BelongsToMany extends Optionable
{
    /**
     * Field component.
     */
    protected static $component = 'select-multiple-field';

    /**
     * Field relationship name.
     */
    public string $belongsToManyRelationship;

    /**
     * Indicates if the field is searchable.
     */
    protected bool $searchable = false;

    /**
     * Initialize new HasMany instance class.
     *
     * @param  string  $attribute
     * @param  string|null  $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->belongsToManyRelationship = $attribute;

        $this->fillUsing(function () {});
    }

    /**
     * Provide the column used for index.
     */
    public function indexColumn(): BelongsToManyColumn
    {
        return new BelongsToManyColumn(
            $this->belongsToManyRelationship,
            $this->labelKey,
            $this->label
        );
    }
}
