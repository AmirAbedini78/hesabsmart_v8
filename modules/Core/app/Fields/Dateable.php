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

trait Dateable
{
    /**
     * Resolve the field value for export.
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return string
     */
    public function resolveForExport($model)
    {
        if (is_callable($this->exportCallback)) {
            return call_user_func_array($this->exportCallback, [$model, $this->resolve($model), $this->attribute]);
        }

        return $model->{$this->attribute};
    }

    /**
     * Mark the field as clearable
     */
    public function clearable(): static
    {
        $this->withMeta(['attributes' => ['clearable' => true]]);

        return $this;
    }
}
