<?php

namespace Tests\Fixtures\Workflows;

use Modules\Contacts\Models\Contact;
use Modules\Core\Contracts\Workflow\FieldChangeTrigger;
use Modules\Core\Contracts\Workflow\ModelTrigger;
use Modules\Core\Fields\Select;
use Modules\Core\Workflow\Trigger;

class ContactUserChangedTrigger extends Trigger implements FieldChangeTrigger, ModelTrigger
{
    /**
     * Trigger name
     */
    public static function name(): string
    {
        return 'Contact user changed';
    }

    /**
     * The trigger related model
     */
    public static function model(): string
    {
        return Contact::class;
    }

    /**
     * The field to track changes on
     */
    public static function field(): string
    {
        return 'user_id';
    }

    /**
     * Provide the change values the user to choose from
     *
     * @return \Modules\Core\Fields\Select
     */
    public static function changeField()
    {
        return Select::make('owner')
            ->labelKey('name')
            ->valueKey('id')
            ->options(function () {
                return []; // not used atm
            });
    }

    /**
     * Trigger available actions
     */
    public function actions(): array
    {
        return [
            new CreateDealAction,
            new CreateActivityAction,
        ];
    }
}
