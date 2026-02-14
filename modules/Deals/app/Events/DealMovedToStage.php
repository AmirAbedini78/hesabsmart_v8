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

namespace Modules\Deals\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Deals\Models\Deal;
use Modules\Deals\Models\Stage;

class DealMovedToStage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create new DealMovedToStage instance.
     */
    public function __construct(public Deal $deal, public Stage $previousStage) {}
}
