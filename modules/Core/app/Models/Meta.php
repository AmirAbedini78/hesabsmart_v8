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

namespace Modules\Core\Models;

use Plank\Metable\Meta as BaseMetaModel;

class Meta extends BaseMetaModel
{
    /**
     * Mutator for value.
     */
    public function setValueAttribute(mixed $value): void
    {
        parent::setValueAttribute($value);

        if ($this->attributes['numeric_value'] === INF) {
            $this->attributes['numeric_value'] = null;
        }
    }
}
