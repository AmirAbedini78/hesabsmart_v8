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

namespace Modules\Core\Common\OAuth\State;

use Illuminate\Support\Manager;
use Modules\Core\Common\OAuth\State\StorageDrivers\Session;

class StateStorageManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['core.oauth.state.storage'];
    }

    /**
     * Create the session driver
     *
     * @return Session
     */
    public function createSessionDriver()
    {
        return new Session;
    }
}
