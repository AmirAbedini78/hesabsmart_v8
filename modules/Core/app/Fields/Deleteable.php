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

use Modules\Core\Models\Model;

trait Deleteable
{
    /**
     * The delete callback.
     *
     * @var null|callable
     */
    protected $deleteCallback = null;

    /**
     * Specify a callback to be called when deleting the field related model.
     */
    public function deleteUsing(callable $callback): static
    {
        $this->deleteCallback = $callback;

        return $this;
    }

    /**
     * Handle the field model deletition.
     */
    public function delete(Model $model): void
    {
        if (is_callable($this->deleteCallback)) {
            call_user_func_array($this->deleteCallback, [$model]);
        }
    }
}
