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

namespace Modules\Activities\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Model;

class Guest extends Model
{
    use SoftDeletes;

    public function guestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(\Modules\Activities\Models\Activity::class, 'activity_guest');
    }
}
