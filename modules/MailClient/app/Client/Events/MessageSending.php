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

namespace Modules\MailClient\Client\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\MailClient\Client\Client;

class MessageSending
{
    use Dispatchable;

    /**
     * Create new MessageSending instance.
     */
    public function __construct(public Client $client) {}
}
