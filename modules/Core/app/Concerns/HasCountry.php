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

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @mixin \Modules\Core\Models\Model */
trait HasCountry
{
    /**
     * A model belongs to country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Country::class);
    }
}
