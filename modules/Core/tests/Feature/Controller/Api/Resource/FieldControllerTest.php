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

namespace Modules\Core\Tests\Feature\Controller\Api\Resource;

use Modules\Core\Facades\Fields;
use Modules\Core\Fields\Email;
use Modules\Core\Fields\ID;
use Modules\Core\Fields\Text;
use Tests\Fixtures\Event;
use Tests\TestCase;

class FieldControllerTest extends TestCase
{
    public function test_resource_create_fields_can_be_retrieved(): void
    {
        $this->signIn();

        Fields::replace('events', [
            Text::make('title'),
            Text::make('description'),
            Email::make('email')->hideWhenCreating(),
        ]);

        $this->getJson('/api/events/create-fields')->assertJsonCount(2);
    }

    public function test_resource_update_fields_can_be_retrieved(): void
    {
        $this->signIn();

        $event = Event::factory()->create();

        Fields::replace('events', [
            Text::make('title'),
            Text::make('description')->hideWhenUpdating(),
        ]);

        $this->getJson('/api/events/'.$event->id.'/update-fields?intent=update')->assertJsonCount(1);
    }

    public function test_resource_detail_fields_can_be_retrieved(): void
    {
        $this->signIn();

        $event = Event::factory()->create();

        Fields::replace('events', [
            Text::make('title'),
            Text::make('description')->hideFromDetail(),
        ]);

        $this->getJson('/api/events/'.$event->id.'/detail-fields?intent=detail')->assertJsonCount(1);
    }

    public function test_resource_index_fields_can_be_retrieved(): void
    {
        $this->signIn();

        Fields::replace('events', [
            ID::make(),
            Text::make('title'),
            Text::make('description')->excludeFromIndex(),
        ]);

        $this->getJson('/api/events/index-fields')->assertJsonCount(2);
    }

    public function test_it_applies_customized_attributes_when_index_fields_are_intended_for_update(): void
    {
        $this->signIn();

        $event = Event::factory()->create();

        Fields::replace('events', [
            ID::make(),
            Text::make('title'),
        ]);

        Fields::customize(['title' => ['isRequired' => true]], 'events', Fields::UPDATE_VIEW);

        $this->getJson('/api/events/index-fields?intent=update&resourceId='.$event->id)
            ->assertJsonCount(2)
            ->assertJsonPath('1.isRequired', true);
    }

    public function test_unauthorized_user_cannot_see_fields_that_is_not_allowed_to_see(): void
    {
        $this->asRegularUser()->signIn();

        $event = Event::factory()->create();

        Fields::replace('events', function () {
            return [
                Text::make('test', 'test'),
                Text::make('test', 'test')->canSeeWhen('DUMMY_ABILITY', 'DUMMY_MODEL'),
                Text::make('test', 'test')->canSee(function () {
                    return false;
                }),
            ];
        });

        $this->getJson('/api/events/'.$event->id.'/update-fields')->assertJsonCount(1);
    }

    public function test_super_admin_can_see_all_fields_that_are_authorized_via_gate(): void
    {
        $this->signIn();

        Fields::replace('events', function () {
            return [
                Text::make('test', 'test')->canSeeWhen('DUMMY_ABILITY', 'DUMMY_MODEL'),
                Text::make('test', 'test')->canSeeWhen('DUMMY_ABILITY', 'DUMMY_MODEL'),
            ];
        });

        $this->getJson('/api/events/create-fields')->assertJsonCount(2);
    }

    public function test_super_admin_cannot_see_fields_that_are_authorized_via_closure_by_returning_false(): void
    {
        $this->signIn();

        Fields::replace('events', function () {
            return [
                Text::make('test', 'test'),
                Text::make('test', 'test')->canSee(function () {
                    // If returned false directly and the check is not
                    // performed via gate, it should not be visible to super
                    // admin either
                    return false;
                }),
            ];
        });

        $this->getJson('/api/events/create-fields')->assertJsonCount(1);
    }

    public function test_resource_export_fields_can_be_retrieved(): void
    {
        $this->signIn();

        Fields::replace('events', [
            Text::make('title'),
            Text::make('description')->excludeFromExport(),
        ]);

        $this->getJson('/api/events/export-fields')->assertJsonCount(1);
    }

    public function test_it_shows_404_for_non_exportable_resources(): void
    {
        $this->signIn();

        Fields::replace('posts', [
            Text::make('title'),
        ]);

        $this->getJson('/api/posts/export-fields')->assertNotFound();
    }
}
