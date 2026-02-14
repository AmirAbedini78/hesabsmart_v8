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

namespace Modules\Core\Common\Media;

use Modules\Core\Models\PendingMedia;

class PruneStaleMediaAttachments
{
    /**
     * Prune the stale attached media from the system.
     */
    public function __invoke(): void
    {
        PendingMedia::with('attachment')
            ->orderByDesc('id')
            ->where('created_at', '<=', now()->subDays(1))
            ->get()
            ->each(function (PendingMedia $media) {
                $media->purge();
            });
    }
}
