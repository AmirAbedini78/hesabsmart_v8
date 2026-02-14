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

class UpdateFinalized
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Initialize new UpdateFinalized instance.
     */
    public function __construct(public string $version, public string $oldVersion) {}

    /**
     * Get the version number the installation was updated to.
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
