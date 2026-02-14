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

namespace Modules\Core\Common\OAuth\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\OAuthAccount;

class OAuthAccountConnected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create new instance of OAuthAccountConnected.
     */
    public function __construct(public OAuthAccount $account) {}
}
