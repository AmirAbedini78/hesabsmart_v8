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

namespace Modules\Core\Contracts\Synchronization;

use Modules\Core\Models\Synchronization;

interface SynchronizesViaWebhook
{
    /**
     * Subscribe for changes for the given synchronization
     */
    public function watch(Synchronization $synchronization): void;

    /**
     * Unsubscribe from changes for the given synchronization
     */
    public function unwatch(Synchronization $synchronization): void;
}
