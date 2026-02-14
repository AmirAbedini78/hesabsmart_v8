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

namespace Modules\Calls\VoIP;

use Illuminate\Support\Manager;
use Modules\Calls\VoIP\Clients\Twilio;
use Modules\Calls\VoIP\Contracts\ReceivesEvents;

class VoIPManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['voip.client'];
    }

    /**
     * Create Twilio VoIP driver
     *
     * @return \Modules\Calls\VoIP\Clients\Twilio
     */
    public function createTwilioDriver()
    {
        return new Twilio($this->container['config']['twilio']);
    }

    /**
     * Check whether the driver receives events
     *
     * @param  string|null  $driver
     * @return bool
     */
    public function shouldReceivesEvents($driver = null)
    {
        return $this->driver($driver) instanceof ReceivesEvents;
    }
}
