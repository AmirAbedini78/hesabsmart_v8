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

namespace Modules\Core\Concerns;

use Illuminate\Support\Str;

/** @mixin \Modules\Core\Models\Model */
trait HasUuid
{
    /**
     * Boot the model uuid generator trait
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function (self $model) {
            if (is_null($model->{$model->uuidColumn()})) {
                $model->forceFill([
                    $model->uuidColumn() => $model->generateUuid(),
                ]);
            }
        });
    }

    /**
     * Generate model uuid.
     */
    public function generateUuid(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Get the model uuid column name.
     */
    public function uuidColumn(): string
    {
        return 'uuid';
    }
}
