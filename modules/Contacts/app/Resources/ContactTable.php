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

namespace Modules\Contacts\Resources;

use Modules\Contacts\Models\Contact;
use Modules\Core\Table\Table;

class ContactTable extends Table
{
    /**
     * Whether the table has actions column.
     */
    public bool $withActionsColumn = true;

    /**
     * Indicates whether the table has views.
     */
    public bool $withViews = true;

    /**
     * Prepare the searchable columns for the model from the table defined columns.
     */
    public function prepareSearchableColumns(): array
    {
        return array_merge(
            parent::prepareSearchableColumns(),
            ['full_name' => [
                'column' => Contact::nameQueryExpression(),
                'condition' => 'like',
            ]],
        );
    }
}
