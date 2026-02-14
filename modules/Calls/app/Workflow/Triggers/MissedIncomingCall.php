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

namespace Modules\Calls\Workflow\Triggers;

use Modules\Activities\Workflow\Actions\CreateActivityAction;
use Modules\Calls\VoIP\Events\IncomingCallMissed;
use Modules\Core\Contracts\Workflow\EventTrigger;
use Modules\Core\Workflow\Actions\WebhookAction;
use Modules\Core\Workflow\Trigger;
use Modules\MailClient\Workflow\Actions\SendEmailAction;

class MissedIncomingCall extends Trigger implements EventTrigger
{
    /**
     * Trigger name
     */
    public static function name(): string
    {
        return __('calls::call.workflows.triggers.missed_incoming_call');
    }

    /**
     * The event name the trigger should be triggered
     */
    public static function event(): string
    {
        return IncomingCallMissed::class;
    }

    /**
     * Provide the trigger available actions
     */
    public function actions(): array
    {
        return [
            (new CreateActivityAction)->executing(function ($action) {
                $call = $action->event->call->toArray();
                $action->activity_title .= ' ['.$call['from'].']';
                if (! empty($action->note)) {
                    $action->note = $action->note.'<br />============<br />';
                }
                $action->note .= 'From: '.$call['from'].'<br />';
                $action->note .= 'To: '.$call['to'].'<br />';
                $action->note .= 'Status: '.$call['status'].'<br />';
            })->withoutDynamicUsers(),
            new SendEmailAction,
            (new WebhookAction)->executing(function ($action) {
                $action->setPayload($action->event->call->toArray());
            }),
        ];
    }
}
