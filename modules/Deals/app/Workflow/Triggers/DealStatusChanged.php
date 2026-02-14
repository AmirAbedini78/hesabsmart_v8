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

namespace Modules\Deals\Workflow\Triggers;

use Modules\Activities\Workflow\Actions\CreateActivityAction;
use Modules\Activities\Workflow\Actions\DeleteAssociatedActivities;
use Modules\Activities\Workflow\Actions\MarkAssociatedActivitiesAsComplete;
use Modules\Core\Contracts\Workflow\FieldChangeTrigger;
use Modules\Core\Contracts\Workflow\ModelTrigger;
use Modules\Core\Fields\Select;
use Modules\Core\Workflow\Actions\WebhookAction;
use Modules\Core\Workflow\Trigger;
use Modules\Deals\Enums\DealStatus;
use Modules\MailClient\Workflow\Actions\ResourcesSendEmailToField;
use Modules\MailClient\Workflow\Actions\SendEmailAction;

class DealStatusChanged extends Trigger implements FieldChangeTrigger, ModelTrigger
{
    /**
     * Trigger name
     */
    public static function name(): string
    {
        return __('deals::deal.workflows.triggers.status_changed');
    }

    /**
     * The trigger related model
     */
    public static function model(): string
    {
        return \Modules\Deals\Models\Deal::class;
    }

    /**
     * The field to track changes on
     */
    public static function field(): string
    {
        return 'status';
    }

    /**
     * Provide the change values the user to choose from
     *
     * @return \Modules\Core\Fields\Select
     */
    public static function changeField()
    {
        return Select::make(static::field())->options([
            DealStatus::won->name => DealStatus::won->label(),
            DealStatus::lost->name => DealStatus::lost->label(),
            DealStatus::open->name => DealStatus::open->label(),
        ]);
    }

    /**
     * Trigger available actions
     */
    public function actions(): array
    {
        return [
            new CreateActivityAction,
            (new SendEmailAction)->toResources(ResourcesSendEmailToField::make()->options([
                'contacts' => [
                    'label' => __('deals::deal.workflows.actions.fields.email_to_contact'),
                    'resource' => 'contacts',
                ],
                'companies' => [
                    'label' => __('deals::deal.workflows.actions.fields.email_to_company'),
                    'resource' => 'companies',
                ],
                'user' => [
                    'label' => __('deals::deal.workflows.actions.fields.email_to_owner_email'),
                    'resource' => 'users',
                ],
                'creator' => [
                    'label' => __('deals::deal.workflows.actions.fields.email_to_creator_email'),
                    'resource' => 'users',
                ],
            ])),
            new MarkAssociatedActivitiesAsComplete,
            new DeleteAssociatedActivities,
            new WebhookAction,
        ];
    }
}
