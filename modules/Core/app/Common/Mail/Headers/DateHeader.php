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

namespace Modules\Core\Common\Mail\Headers;

use Carbon\Carbon;

class DateHeader extends Header
{
    /**
     * Get the header value
     */
    public function getValue(?string $tz = null): ?Carbon
    {
        $tz = $tz ?: config('app.timezone');

        $dateString = $this->value;

        // https://github.com/briannesbitt/Carbon/issues/685
        if (is_string($dateString)) {
            $dateString = trim(preg_replace('/\(.*$/', '', $dateString));
        }

        return $dateString ? Carbon::parse($dateString)->tz($tz) : null;
    }
}
