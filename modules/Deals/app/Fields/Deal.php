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

namespace Modules\Deals\Fields;

use Modules\Core\Fields\BelongsTo;
use Modules\Deals\Http\Resources\DealResource;
use Modules\Deals\Models\Deal as DealModel;

/**
 * @codeCoverageIgnore
 */
class Deal extends BelongsTo
{
    /**
     * Create new instance of Deal field
     *
     * @param  string  $relationName  The relation name, snake case format
     * @param  string  $label  Custom label
     * @param  string  $foreignKey  Custom foreign key
     */
    public function __construct($relationName = 'deal', $label = null, $foreignKey = null)
    {
        parent::__construct($relationName, DealModel::class, $label ?? __('deals::deal.deal'), $foreignKey);

        $this->setJsonResource(DealResource::class)
            ->async('/deals/search');
    }
}
