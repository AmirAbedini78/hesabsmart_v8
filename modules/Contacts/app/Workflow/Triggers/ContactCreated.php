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

namespace Modules\Contacts\Workflow\Triggers;

use Modules\Activities\Workflow\Actions\CreateActivityAction;
use Modules\Core\Contracts\Workflow\EventTrigger;
use Modules\Core\Contracts\Workflow\ModelTrigger;
use Modules\Core\Workflow\Actions\WebhookAction;
use Modules\Core\Workflow\Trigger;
use Modules\MailClient\Workflow\Actions\ResourcesSendEmailToField;
use Modules\MailClient\Workflow\Actions\SendEmailAction;

class ContactCreated extends Trigger implements EventTrigger, ModelTrigger
{
    /**
     * Trigger name
     */
    public static function name(): string
    {
        return __('contacts::contact.workflows.triggers.created');
    }

    /**
     * The trigger related model
     */
    public static function model(): string
    {
        return \Modules\Contacts\Models\Contact::class;
    }

    /**
     * The model event trigger
     */
    public static function event(): string
    {
        return 'created';
    }

    /**
     * Trigger available actions
     */
    public function actions(): array
    {
        return [
            new CreateActivityAction,
            (new SendEmailAction)->toResources(ResourcesSendEmailToField::make()->options([
                'self' => [
                    'label' => __('contacts::contact.workflows.actions.fields.email_to_contact'),
                    'resource' => 'contacts',
                ],
                'user' => [
                    'label' => __('contacts::contact.workflows.actions.fields.email_to_owner_email'),
                    'resource' => 'users',
                ],
                'creator' => [
                    'label' => __('contacts::contact.workflows.actions.fields.email_to_creator_email'),
                    'resource' => 'users',
                ],
                'companies' => [
                    'label' => __('contacts::contact.workflows.actions.fields.email_to_company'),
                    'resource' => 'companies',
                ],
            ])),
            new WebhookAction,
        ];
    }
}
