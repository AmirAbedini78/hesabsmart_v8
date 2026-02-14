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

namespace Modules\Updater\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Modules\Updater\Patch;

class PatchApplied
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Initialize new PatchApplied instance.
     */
    public function __construct(public Patch $patch) {}

    /**
     * Get the patch that was applied.
     */
    public function getPatch(): Patch
    {
        return $this->patch;
    }
}
