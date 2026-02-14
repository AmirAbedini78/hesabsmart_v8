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

use Modules\Core\Table\Column;

class ID extends Field
{
    /**
     * Field component.
     */
    protected static $component = 'id-field';

    /**
     * Initialize new ID instance.
     */
    public function __construct(string $attribute = 'id', ?string $label = null)
    {
        parent::__construct($attribute, $label ?: __('core::app.id'));

        $this->exceptOnForms()
            ->readonly(true)
            ->useSearchColumn([$this->attribute => '='])
            ->tapIndexColumn(fn (Column $column) => $column
                ->width('100px')
                ->minWidth('100px')
                ->centered()
            );
    }
}
