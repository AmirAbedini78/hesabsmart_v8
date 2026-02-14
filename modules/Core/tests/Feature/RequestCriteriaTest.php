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

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Request;
use Modules\Core\Criteria\RequestCriteria;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Fixtures\Event;
use Tests\Fixtures\EventStatus;
use Tests\TestCase;

class RequestCriteriaTest extends TestCase
{
    /**
     * @return \Illuminate\Http\Request
     */
    protected function createCriteriaRequest($params)
    {
        $query = (is_array($params) ? http_build_query($params) : $params);

        return Request::create('/fake?'.$query, 'GET');
    }

    protected function query($params, $fields = [])
    {
        $model = new Event;

        $criteria = new RequestCriteria(
            $this->createCriteriaRequest($params),
        );

        $criteria->setSearchFields($fields);

        return $model->newQuery()->criteria($criteria);
    }

    public function test_it_aborts_when_all_provided_fields_are_not_searchable(): void
    {
        Event::factory()->create(['title' => 'title', 'description' => 'description']);

        try {
            $query = $this->query('q=title&search_fields=description', ['title' => '=']);

            $query->get();
        } catch (\Throwable $e) {
        }

        $this->assertEquals(
            new HttpException(403, 'None of the search fields were accepted. Acceptable search fields are: title,id'),
            $e
        );
    }

    public function test_it_accepts_search_fields_in_query_parameter(): void
    {
        Event::factory()->create(['title' => 'Event 1']);
        Event::factory()->create(['description' => 'Event 2']);

        $query = $this->query('q=title:Event 1;description:Event', ['title' => '=', 'description' => 'like']);

        $this->assertCount(2, $query->get());

        Event::factory()->create(['title' => 'unique', 'description' => 'Event 2']);

        $query = $this->query('q=title:John;description:Event 2&search_fields=title:like;description:=', [
            'title' => 'like',
            'description' => '=',
        ]);

        $this->assertCount(2, $query->get());
    }

    public function test_it_accepts_multiple_searchable_fields(): void
    {
        Event::factory()->create(['title' => 'Event', 'description' => 'Description']);
        Event::factory()->create(['description' => 'Event 1']);

        $query = $this->query('q=Event&search_fields=title:=;description:like', ['title' => '=', 'description' => 'like']);

        $this->assertCount(2, $query->get());
    }

    public function test_it_can_specify_the_search_match(): void
    {
        Event::factory()->create(['title' => 'Unique', 'description' => 'Description']);
        Event::factory()->create(['title' => 'Event', 'description' => 'Description']);

        $query = $this->query(
            'q=title:Unique;Description:Description&search_fields=title:like;Description:=&search_match=and', [
                'title' => 'like',
                'Description' => '=',
            ]);

        $this->assertCount(1, $query->get());
    }

    public function test_it_accept_searchable_fields_with_like_operator(): void
    {
        Event::factory()->create(['title' => 'Event 1', 'description' => 'Description 1']);
        Event::factory()->create(['title' => 'Should not be searched', 'description' => 'Event 1']);

        $query = $this->query('q=Event&search_fields=title:like', ['title' => 'like', 'description' => 'like']);

        $this->assertCount(1, $query->get());
    }

    public function test_it_uses_only_the_allowed_search_fields(): void
    {
        Event::factory()->create(['title' => 'Same']);
        Event::factory()->create(['description' => 'Same']);
        $query = $this->query('q=Same&search_fields=title', ['title' => '=']);

        $this->assertCount(1, $query->get());

        $query = $this->query('q=title:Same;description:Same', ['title' => '=']);

        $this->assertCount(1, $query->get());
    }

    public function test_when_no_searchable_fields_provided_it_uses_the_defined_ones(): void
    {
        Event::factory()->create(['title' => 'Event']);

        $query = $this->query('q=Event', ['title' => '=']);

        $this->assertCount(1, $query->get());
    }

    public function test_it_accept_searchable_fields_with_equal_operator(): void
    {
        Event::factory()->create(['title' => 'Event 1']);
        Event::factory()->create(['title' => 'Event 12']);

        $query = $this->query('q=Event 1&search_fields=title:=', ['title' => 'like']);

        $this->assertCount(1, $query->get());
    }

    public function test_it_can_take_specified_number(): void
    {
        Event::factory()->count(3)->create();

        $results = $this->query('take=2')->get();

        $this->assertCount(2, $results);
    }

    public function test_can_eager_load_relations(): void
    {
        Event::factory()->create();

        $results = $this->query('with=status;locations')->get();

        $this->assertTrue($results[0]->relationLoaded('status'));
        $this->assertTrue($results[0]->relationLoaded('locations'));

        // Single
        $results = $this->query('with=status')->get();

        $this->assertTrue($results[0]->relationLoaded('status'));

        // array
        $results = $this->query(['with' => ['status', 'locations']])->get();

        $this->assertTrue($results[0]->relationLoaded('status'));
        $this->assertTrue($results[0]->relationLoaded('locations'));
    }

    public function test_it_selects_only_the_provided_columns(): void
    {
        Event::factory()->create();

        $results = $this->query('select=id;title;description')->get();

        $this->assertNull($results[0]->start);
        $this->assertNull($results[0]->end);
        $this->assertNotNull($results[0]->id);
        $this->assertNotNull($results[0]->title);
        $this->assertNotNull($results[0]->description);

        // array
        $results = $this->query(['select' => ['id', 'title', 'description']])->get();

        $this->assertNull($results[0]->start);
        $this->assertNull($results[0]->end);
        $this->assertNotNull($results[0]->id);
        $this->assertNotNull($results[0]->title);
        $this->assertNotNull($results[0]->description);
    }

    public function test_it_applies_search_and_where_when_relation(): void
    {
        Event::factory()->for(EventStatus::factory([
            'name' => 'Confirmed',
        ]), 'status')->create();

        $query = $this->query('q=status.name:Confirmed&search_match=and', ['status.name' => '=']);

        $this->assertCount(1, $query->get());
    }

    public function test_it_applies_search_or_where_when_relation(): void
    {
        Event::factory()->for(EventStatus::factory([
            'name' => 'Confirmed',
        ]), 'status')->create();

        Event::factory()->create(['title' => 'Event']);

        $query = $this->query('q=title:Event;status.name:Confirmed&search_match=or', ['status.name' => '=', 'title' => '=']);

        $this->assertCount(2, $query->get());
    }

    public function test_it_applies_order_when_table_is_provided(): void
    {
        $event = Event::factory()->for(EventStatus::factory([
            'name' => 'Confirmed',
            'created_at' => now()->addDay(4),
        ]), 'status')->create(['created_at' => now()->addDay(4)]);

        Event::factory()->create(['created_at' => now()->addDay(5)]);

        // With providing the table
        $results = $this->query(['order' => ['field' => 'events.created_at', 'direction' => 'asc']])->get();
        $this->assertEquals($event->id, $results[0]->id);
    }

    public function test_it_applies_order_on_multiple_provided_fields(): void
    {
        $event1 = Event::factory()->create(['title' => 'B', 'created_at' => now()->subDay(3)]);
        $event2 = Event::factory()->create(['created_at' => now()->subDay(5), 'title' => 'C']);
        $event3 = Event::factory()->create(['created_at' => now()->subDay(4), 'title' => 'A']);

        $results = $this->query(['order' => [
            ['field' => 'created_at', 'direction' => 'asc'],
            ['field' => 'title', 'direction' => 'desc'],
        ]])->get();

        $this->assertEquals($event2->id, $results[0]->id);
        $this->assertEquals($event3->id, $results[1]->id);
        $this->assertEquals($event1->id, $results[2]->id);
    }

    public function test_it_applies_the_provided_order(): void
    {
        $event = Event::factory()->create(['created_at' => now()->addDay(1)]);
        Event::factory()->create(['created_at' => now()->subDay(5)]);

        $results = $this->query('order=created_at|desc')->get();

        $this->assertEquals($event->id, $results[0]->id);

        $results = $this->query('order=created_at|asc')->get();
        $this->assertEquals($event->id, $results[1]->id);

        // default asc
        $results = $this->query('order=created_at')->get();
        $this->assertEquals($event->id, $results[1]->id);

        $results = $this->query(['order' => ['field' => 'created_at', 'direction' => 'asc']])->get();
        $this->assertEquals($event->id, $results[1]->id);

        $results = $this->query(['order' => ['field' => 'created_at', 'direction' => 'asc']])->get();
        $this->assertEquals($event->id, $results[1]->id);

        // default asc
        $results = $this->query(['order' => ['field' => 'created_at', 'direction' => '']])->get();
        $this->assertEquals($event->id, $results[1]->id);
    }

    public function test_it_supports_id_field_with_in_condition(): void
    {
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        Event::factory()->create(); // third event that shouldn't match

        // Test with explicit in condition
        $query = $this->query("q={$event1->id},{$event2->id}&search_fields=id:in", ['title' => 'like']);
        $this->assertCount(2, $query->get());

        // Test with auto-adjustment from = to in when comma-separated values
        $query = $this->query("q={$event1->id},{$event2->id}&search_fields=id:=", ['title' => 'like']);
        $this->assertCount(2, $query->get());

        // Test single ID with = condition
        $query = $this->query("q={$event1->id}&search_fields=id:=", ['title' => 'like']);
        $this->assertCount(1, $query->get());
    }

    public function test_it_supports_array_conditions_for_fields(): void
    {
        Event::factory()->create(['title' => 'Exact Match']);
        Event::factory()->create(['title' => 'Partial Match Test']);

        // Test field with array of allowed conditions
        $query = $this->query('q=Exact Match&search_fields=title:=', [
            'title' => ['condition' => ['like', '=']],
        ]);
        $this->assertCount(1, $query->get());

        $query = $this->query('q=Partial&search_fields=title:like', [
            'title' => ['condition' => ['like', '=']],
        ]);
        $this->assertCount(1, $query->get());
    }

    public function test_it_auto_adjusts_equal_to_in_condition_for_comma_separated_ids(): void
    {
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        Event::factory()->create(); // third event that shouldn't match

        // Test auto-adjustment from = to in for ID field with comma-separated values
        $query = $this->query("q={$event1->id},{$event2->id}&search_fields=id:=", ['title' => 'like']);
        $this->assertCount(2, $query->get());
    }

    public function test_it_does_not_auto_adjust_non_id_fields(): void
    {
        Event::factory()->create(['title' => 'Event,Test']);
        Event::factory()->create(['title' => 'Event']);
        Event::factory()->create(['title' => 'Test']);

        // Non-ID fields should not auto-adjust from = to in
        $query = $this->query('q=Event,Test&search_fields=title:=', ['title' => ['condition' => ['=', 'like']]]);
        $this->assertCount(1, $query->get()); // Only exact match
    }

    public function test_id_field_automatically_supports_in_and_equal_conditions(): void
    {
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        Event::factory()->create();

        // ID field should support 'in' condition even if not explicitly defined
        $query = $this->query("q={$event1->id},{$event2->id}&search_fields=id:in", ['title' => 'like']);
        $this->assertCount(2, $query->get());

        // ID field should support '=' condition
        $query = $this->query("q={$event1->id}&search_fields=id:=", ['title' => 'like']);
        $this->assertCount(1, $query->get());
    }

    public function test_like_fields_automatically_support_equal_condition(): void
    {
        Event::factory()->create(['title' => 'Event 1']);
        Event::factory()->create(['title' => 'Event 12']);

        // Field defined with 'like' should also support '=' when requested
        $query = $this->query('q=Event 1&search_fields=title:=', ['title' => 'like']);
        $this->assertCount(1, $query->get());

        // Should still work with 'like' condition
        $query = $this->query('q=Event&search_fields=title:like', ['title' => 'like']);
        $this->assertCount(2, $query->get());
    }

    public function test_it_processes_in_condition_with_array_values_correctly(): void
    {
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        $event3 = Event::factory()->create();

        // Test that 'in' condition with array values doesn't get skipped
        $query = $this->query("q={$event1->id},{$event2->id}&search_fields=id:in", ['title' => 'like']);
        $results = $query->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $event1->id));
        $this->assertTrue($results->contains('id', $event2->id));
        $this->assertFalse($results->contains('id', $event3->id));
    }

    public function test_it_validates_conditions_against_allowed_conditions(): void
    {
        Event::factory()->create(['title' => 'Test']);

        // Should accept valid condition
        $query = $this->query('q=Test&search_fields=title:like', [
            'title' => ['condition' => ['like', '=']],
        ]);
        $this->assertCount(1, $query->get());

        // Should reject invalid condition and throw exception
        try {
            $query = $this->query('q=Test&search_fields=title:invalid', [
                'title' => ['condition' => ['like', '=']],
            ]);
            $query->get();
            $this->fail('Expected HttpException was not thrown');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
            $this->assertStringContainsString('None of the search fields were accepted', $e->getMessage());
        }
    }

    public function test_it_handles_complex_field_definitions(): void
    {
        Event::factory()->create(['title' => 'Test Event', 'description' => 'Description']);

        // Test complex field definition with custom column and multiple conditions
        $query = $this->query('q=Test&search_fields=custom_title:like', [
            'custom_title' => [
                'column' => 'title',
                'condition' => ['like', '='],
            ],
        ]);
        $this->assertCount(1, $query->get());
    }

    public function test_it_supports_mixed_field_definitions(): void
    {
        $event1 = Event::factory()->create(['title' => 'Exact Title']);
        $event2 = Event::factory()->create(['description' => 'Contains Test Description']);

        // Test mixing simple string conditions with array conditions
        $query = $this->query('q=title:Exact Title;description:Test&search_fields=title:=;description:like', [
            'title' => '=',  // Simple string condition
            'description' => ['condition' => ['like', '=']],  // Array condition
        ]);

        $this->assertCount(2, $query->get());
    }

    public function test_it_preserves_original_condition_when_no_override(): void
    {
        Event::factory()->create(['title' => 'Event Test']);
        Event::factory()->create(['title' => 'Event Test Extra']);

        // When no search_fields override is provided, should use original condition
        $query = $this->query('q=Event', ['title' => 'like']);
        $this->assertCount(2, $query->get()); // Both should match with like

        $query = $this->query('q=Event Test', ['title' => '=']);
        $this->assertCount(1, $query->get()); // Only exact match
    }

    public function test_it_handles_case_insensitive_conditions(): void
    {
        Event::factory()->create(['title' => 'Test Event']);

        // Test case insensitive condition matching
        $query = $this->query('q=Test Event&search_fields=title:LIKE', ['title' => 'like']);
        $this->assertCount(1, $query->get());

        $query = $this->query('q=Test Event&search_fields=title:Like', ['title' => 'like']);
        $this->assertCount(1, $query->get());
    }
}
