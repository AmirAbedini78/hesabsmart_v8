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

namespace Modules\Core\Contracts\Resources;

use Modules\Core\Models\Model;

interface Cloneable
{
    /**
     * Clone the resource record from the given id
     */
    public function clone(Model $model, int $userId): Model;
}
