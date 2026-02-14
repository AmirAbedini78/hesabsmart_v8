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

namespace Modules\Contacts\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Contacts\Enums\PhoneType;
use Modules\Contacts\Models\Phone;
use Modules\Core\Models\Model;

/** @mixin \Modules\Core\Models\Model */
trait HasPhones
{
    /**
     * Boot the HasPhones trait
     */
    protected static function bootHasPhones(): void
    {
        static::deleting(function (Model $model) {
            if ($model->isReallyDeleting()) {
                $model->phones()->delete();
            }
        });
    }

    /**
     * A model has phone number
     */
    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'phoneable')->orderBy('phones.created_at');
    }

    /**
     * Scope a query to include records by phone.
     */
    public function scopeByPhone(Builder $query, string $phone, ?PhoneType $type = null): void
    {
        $query->whereHas('phones', function ($query) use ($phone, $type) {
            if ($type) {
                $query->where('type', $type);
            }

            return $query->where('number', $phone);
        });
    }
}
