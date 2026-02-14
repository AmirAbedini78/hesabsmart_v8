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

namespace Modules\Core\Workflow;

use Illuminate\Events\Dispatcher;
use Modules\Core\Models\Workflow;

class WorkflowEventsSubscriber
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events)
    {
        foreach (Workflows::$eventOnlyListeners as $data) {
            $events->listen($data['event'], function ($event) use ($data) {
                $workflows = Workflow::byTrigger($data['trigger'])->get();

                foreach ($workflows as $workflow) {
                    Workflows::process($workflow, ['event' => $event]);
                }
            });
        }
    }
}
