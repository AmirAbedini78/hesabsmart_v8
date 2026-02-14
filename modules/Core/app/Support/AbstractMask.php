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

namespace Modules\Core\Support;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

abstract class AbstractMask implements Arrayable, JsonSerializable
{
    /**
     * Initialize the mask
     *
     * @param  array|object  $entity
     */
    public function __construct(protected $entity) {}

    /**
     * Get the entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
