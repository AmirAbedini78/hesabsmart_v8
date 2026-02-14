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

use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\Fixtures\Event;
use Tests\Fixtures\EventResource;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_the_resource_search_endpoints(): void
    {
        $this->json('GET', '/api/events/search')->assertUnauthorized();
    }

    public function test_non_searchable_resource_cannot_be_searched(): void
    {
        $this->signIn();

        EventResource::swapFields([]);

        $this->json('GET', '/api/events/search?q=test')
            ->assertNotFound();
    }

    public function test_own_criteria_is_applied_on_resource_search(): void
    {
        $user = $this->asRegularUser()->withPermissionsTo('view own events')->signIn();

        Event::factory()->count(2)->state(new Sequence(
            ['title' => 'John', 'user_id' => $user->getKey()],
            ['title' => 'John', 'user_id' => null]
        ))->create();

        $this->getJson('/api/events/search?q=john')
            ->assertJsonCount(1);
    }

    public function test_it_does_not_return_any_results_if_search_query_is_empty(): void
    {
        $this->signIn();

        Event::factory()->create();

        $this->json('GET', '/api/contacts/search?q=')
            ->assertJsonCount(0);
    }
}
