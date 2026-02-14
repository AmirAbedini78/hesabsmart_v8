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

namespace Modules\Core\Tests\Feature\Filters;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Modules\Core\Filters\Number;
use Modules\Core\Tests\Concerns\TestsFilters;
use Tests\Fixtures\Event;
use Tests\TestCase;

class NumberFilterTest extends TestCase
{
    use TestsFilters;

    protected static $filter = Number::class;

    public function test_number_filter_rule_with_equal_operator(): void
    {
        Event::factory()->count(2)->state(new Sequence(
            ['total_guests' => 1],
            ['total_guests' => 2]
        ))->create();

        $result = $this->perform('total_guests', 'equal', 2);

        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->total_guests, 2);
    }

    public function test_number_filter_rule_with_not_equal_operator(): void
    {
        Event::factory()->count(2)->state(new Sequence(
            ['total_guests' => 1],
            ['total_guests' => 2]
        ))->create();

        $result = $this->perform('total_guests', 'not_equal', 2);

        $this->assertCount(1, $result);
        $this->assertEquals($result[0]->total_guests, 1);
    }

    public function test_number_filter_rule_with_less_operator(): void
    {
        Event::factory()->count(1)->state(new Sequence(
            ['total_guests' => 5]
        ))->create();

        $result = $this->perform('total_guests', 'less', 5);

        $this->assertCount(0, $result);
    }

    public function test_number_filter_rule_with_less_or_equal_operator(): void
    {
        Event::factory()->count(3)->state(new Sequence(
            ['total_guests' => 20],
            ['total_guests' => 80],
            ['total_guests' => 50]
        ))->create();

        $result = $this->perform('total_guests', 'less_or_equal', 50);

        $this->assertCount(2, $result);
    }

    public function test_number_filter_rule_with_greater_operator(): void
    {
        Event::factory()->count(3)->state(new Sequence(
            ['total_guests' => 20],
            ['total_guests' => 80],
            ['total_guests' => 50]
        ))->create();

        $result = $this->perform('total_guests', 'greater', 20);

        $this->assertCount(2, $result);
    }

    public function test_number_filter_rule_with_greater_or_equal_operator(): void
    {
        Event::factory()->count(3)->state(new Sequence(
            ['total_guests' => 20],
            ['total_guests' => 80],
            ['total_guests' => 50]
        ))->create();

        $result = $this->perform('total_guests', 'greater_or_equal', 50);

        $this->assertCount(2, $result);
    }

    public function test_number_filter_rule_with_between_operator(): void
    {
        Event::factory()->count(3)->state(new Sequence(
            ['total_guests' => 20],
            ['total_guests' => 30],
            ['total_guests' => 40]
        ))->create();

        $result = $this->perform('total_guests', 'between', [20, 30]);

        $this->assertCount(2, $result);
    }

    public function test_number_filter_rule_with_not_between_operator(): void
    {
        Event::factory()->count(3)->state(new Sequence(
            ['total_guests' => 20],
            ['total_guests' => 30],
            ['total_guests' => 40]
        ))->create();

        $result = $this->perform('total_guests', 'not_between', [20, 30]);

        $this->assertCount(1, $result);
    }

    public function test_number_filter_rule_with_is_null_operator(): void
    {
        Event::factory()->count(3)->state(new Sequence(
            ['date' => null],
            ['date' => date('Y-m-d')],
            ['date' => date('Y-m-d')]
        ))->create();

        $result = $this->perform('date', 'is_null');

        $this->assertCount(1, $result);
    }

    public function test_number_filter_rule_with_is_not_null_operator(): void
    {
        Event::factory()->count(3)->state(new Sequence(
            ['date' => null],
            ['date' => null],
            ['date' => date('Y-m-d')]
        ))->create();

        $result = $this->perform('date', 'is_not_null');

        $this->assertCount(1, $result);
    }
}
