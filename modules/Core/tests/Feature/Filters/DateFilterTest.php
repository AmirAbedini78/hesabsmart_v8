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
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Modules\Core\Filters\Date;
use Modules\Core\Filters\DateTime;
use Modules\Core\Tests\Concerns\TestsFilters;
use Tests\Fixtures\Event;
use Tests\TestCase;

class DateFilterTest extends TestCase
{
    use TestsFilters;

    protected static $filter;

    public function test_date_filter_rule_with_equal_operator(): void
    {
        static::$filter = DateTime::class;

        Event::factory()->count(2)->state(new Sequence(
            ['start' => $lastWeek = date('Y-m-d', strtotime('last week'))],
            ['start' => date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('start', 'equal', $lastWeek);

        $this->assertCount(1, $result);

        static::$filter = Date::class;

        Event::factory()->count(2)->state(new Sequence(
            ['date' => $lastWeek = date('Y-m-d', strtotime('last week'))],
            ['date' => date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('date', 'equal', $lastWeek);

        $this->assertCount(1, $result);
    }

    public function test_date_filter_rule_with_not_equal_operator(): void
    {
        static::$filter = DateTime::class;

        Event::factory()->count(2)->state(new Sequence(
            ['start' => $lastWeek = date('Y-m-d', strtotime('last week'))],
            ['start' => date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('start', 'not_equal', $lastWeek);

        $this->assertCount(1, $result);

        static::$filter = Date::class;

        Event::factory()->count(2)->state(new Sequence(
            ['date' => $lastWeek = date('Y-m-d', strtotime('last week'))],
            ['date' => date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('date', 'not_equal', $lastWeek);

        $this->assertCount(1, $result);
    }

    public function test_date_filter_rule_with_less_operator(): void
    {
        static::$filter = DateTime::class;

        Event::factory()->count(2)->state(new Sequence(
            ['start' => date('Y-m-d', strtotime('last week'))],
            ['start' => $nextWeek = date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('start', 'less', $nextWeek);

        $this->assertCount(1, $result);

        static::$filter = Date::class;

        Event::factory()->count(2)->state(new Sequence(
            ['date' => date('Y-m-d', strtotime('last week'))],
            ['date' => $nextWeek = date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('date', 'less', $nextWeek);

        $this->assertCount(1, $result);
    }

    public function test_date_filter_rule_with_less_or_equal_operator(): void
    {
        static::$filter = DateTime::class;

        Event::factory()->count(3)->state(new Sequence(
            ['start' => date('Y-m-d', strtotime('last week'))],
            ['start' => date('Y-m-d', strtotime('second day next week'))],
            ['start' => $nextWeek = date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('start', 'less_or_equal', $nextWeek);

        $this->assertCount(2, $result);

        static::$filter = Date::class;

        Event::factory()->count(3)->state(new Sequence(
            ['date' => date('Y-m-d', strtotime('last week'))],
            ['start' => date('Y-m-d', strtotime('second day next week'))],
            ['date' => $nextWeek = date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('date', 'less_or_equal', $nextWeek);

        $this->assertCount(2, $result);
    }

    public function test_date_filter_rule_with_greater_operator(): void
    {
        static::$filter = DateTime::class;

        Event::factory()->count(2)->state(new Sequence(
            ['start' => $lastWeek = date('Y-m-d', strtotime('last week'))],
            ['start' => date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('start', 'greater', $lastWeek);

        $this->assertCount(1, $result);

        static::$filter = Date::class;

        Event::factory()->count(2)->state(new Sequence(
            ['date' => $lastWeek = date('Y-m-d', strtotime('last week'))],
            ['date' => date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('date', 'greater', $lastWeek);

        $this->assertCount(1, $result);
    }

    public function test_date_filter_rule_with_greater_or_equal_operator(): void
    {
        static::$filter = DateTime::class;

        Event::factory()->count(3)->state(new Sequence(
            ['start' => date('Y-m-d', strtotime('last month'))],
            ['start' => date('Y-m-d', strtotime('second day next week'))],
            ['start' => $nextWeek = date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('start', 'greater_or_equal', $nextWeek);

        $this->assertCount(2, $result);

        static::$filter = Date::class;

        Event::factory()->count(3)->state(new Sequence(
            ['date' => date('Y-m-d', strtotime('last month'))],
            ['date' => date('Y-m-d', strtotime('second day next week'))],
            ['date' => $nextWeek = date('Y-m-d', strtotime('next week'))]
        ))->create();

        $result = $this->perform('date', 'greater_or_equal', $nextWeek);

        $this->assertCount(2, $result);
    }

    public function test_date_filter_rule_with_between_operator(): void
    {
        static::$filter = DateTime::class;

        $thisMonth = date('Y-m-d', strtotime('last month'));
        $nextMonth = date('Y-m-d', strtotime('next month'));

        Event::factory()->count(4)->state(new Sequence(
            ['start' => date('Y-m-d', strtotime('last year'))],
            ['start' => date('Y-m-d', strtotime('last year'))],
            ['start' => $thisMonth],
            ['start' => $nextMonth]
        ))->create();

        $result = $this->perform('start', 'between', [$thisMonth, $nextMonth]);

        $this->assertCount(2, $result);

        static::$filter = Date::class;

        Event::factory()->count(3)->state(new Sequence(
            ['date' => date('Y-m-d', strtotime('last year'))],
            ['date' => date('Y-m-d', strtotime('last year'))],
            ['date' => $thisMonth],
            ['date' => $nextMonth]
        ))->create();

        $result = $this->perform('date', 'between', [$thisMonth, $nextMonth]);
    }

    public function test_date_filter_rule_with_not_between_operator(): void
    {
        static::$filter = DateTime::class;

        $thisMonth = date('Y-m-d', strtotime('last month'));
        $nextMonth = date('Y-m-d', strtotime('next month'));

        Event::factory()->count(4)->state(new Sequence(
            ['start' => date('Y-m-d', strtotime('last year'))],
            ['start' => date('Y-m-d', strtotime('last year'))],
            ['start' => $thisMonth],
            ['start' => $nextMonth]
        ))->create();

        $result = $this->perform('start', 'not_between', [$thisMonth, $nextMonth]);

        $this->assertCount(2, $result);

        static::$filter = Date::class;

        Event::factory()->count(3)->state(new Sequence(
            ['date' => date('Y-m-d', strtotime('last year'))],
            ['date' => date('Y-m-d', strtotime('last year'))],
            ['date' => $thisMonth],
            ['date' => $nextMonth]
        ))->create();

        $result = $this->perform('date', 'not_between', [$thisMonth, $nextMonth]);
    }

    public function test_date_filter_rule_with_is_operator(): void
    {
        // Create floating event so we can always have returned 1 result based on the applied filter
        // If this event is queried, means that the test will probably fail
        Event::factory()->create([
            'start' => date('Y-m-d H:i:s', strtotime('25 years ago')),
            'end' => date('Y-m-d H:i:s', strtotime('25 years ago')),
        ]);

        foreach ([Date::class, DateTime::class] as $filter) {
            static::$filter = $filter;

            $attribute = is_a($filter, Date::class, true) ? 'date' : 'start';

            $isOperatorOptions = app($filter, [
                'field' => $attribute,
            ])->isOperatorOptions();

            foreach ($isOperatorOptions as $isOperatorValue => $label) {
                if (in_array($isOperatorValue, ['past', 'future'])) {
                    continue;
                }

                $betweenArguments = $this->getBetweenArguments($isOperatorValue);

                $event = Event::factory()->create([
                    $attribute => $betweenArguments[0],
                ]);

                $result = $this->perform($attribute, 'is', $isOperatorValue);
                $event->delete();
                $this->assertCount(1, $result);
            }
        }
    }

    public function test_date_filter_rule_with_is_past_operator(): void
    {
        static::$filter = Date::class;

        Event::factory()->create([
            'start' => date('Y-m-d H:i:s', strtotime('25 years ago')),
        ]);

        Event::factory()->create([
            'start' => date('Y-m-d H:i:s', strtotime('next year')),
        ]);

        $result = $this->perform('start', 'is', 'past');
        $this->assertCount(1, $result);
    }

    public function test_datetime_filter_rule_with_is_past_operator(): void
    {
        static::$filter = DateTime::class;

        Event::factory()->create([
            'start' => date('Y-m-d H:i:s', strtotime('25 years ago')),
        ]);

        Event::factory()->create([
            'start' => date('Y-m-d H:i:s', strtotime('next year')),
        ]);

        $result = $this->perform('start', 'is', 'past');
        $this->assertCount(1, $result);
    }

    public function test_date_filter_rule_with_is_future_operator(): void
    {
        static::$filter = Date::class;

        Event::factory()->create([
            'start' => date('Y-m-d H:i:s', strtotime('25 years ago')),
        ]);

        Event::factory()->create([
            'start' => date('Y-m-d H:i:s', strtotime('next year')),
        ]);

        $result = $this->perform('start', 'is', 'future');
        $this->assertCount(1, $result);
    }

    public function test_datetime_filter_rule_with_is_future_operator(): void
    {
        static::$filter = DateTime::class;

        Event::factory()->create([
            'start' => date('Y-m-d H:i:s', strtotime('25 years ago')),
        ]);

        Event::factory()->create([
            'start' => date('Y-m-d H:i:s', strtotime('next year')),
        ]);

        $result = $this->perform('start', 'is', 'future');
        $this->assertCount(1, $result);
    }

    public function test_date_filter_does_not_throw_error_when_not_value_is_passed(): void
    {
        static::$filter = DateTime::class;

        $result = $this->perform('start', 'equal', '');
        $this->assertCount(0, $result);

        static::$filter = Date::class;

        $result = $this->perform('start', 'equal', '');
        $this->assertCount(0, $result);
    }

    protected function getBetweenArguments(string $range)
    {
        $tz = tz()->current();
        $now = Carbon::asCurrentTimezone();

        $period = match ($range) {
            'today' => [$now->copy()->startOfDay(), $now->endOfDay()],
            'yesterday' => [$now->copy()->yesterday($tz)->startOfDay(), $now->yesterday($tz)->endOfDay()],
            'next_day' => [$now->copy()->tomorrow($tz)->startOfDay(), $now->tomorrow($tz)->endOfDay()],
            'this_week' => [$now->copy()->startOfWeek(), $now->endOfWeek()],
            'last_week' => [$now->copy()->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()],
            'next_week' => [$now->copy()->addWeek()->startOfWeek(), $now->addWeek()->endOfWeek()],
            'this_month' => [$now->copy()->startOfMonth(), $now->endOfMonth()],
            // https://github.com/briannesbitt/Carbon/issues/639
            'last_month' => [
                $now->copy()->startOfMonth()->subMonth()->startOfMonth(),
                $now->startOfMonth()->subMonth()->endOfMonth(),
            ],
            'next_month' => [$now->copy()->startOfMonth()->addMonth(), $now->startOfMonth()->addMonth()->endOfMonth()],
            'this_quarter' => [$now->copy()->startOfQuarter(), $now->endOfQuarter()],
            'last_quarter' => [$now->copy()->subQuarter()->startOfQuarter(), $now->subQuarter()->endOfQuarter()],
            'next_quarter' => [$now->copy()->addQuarter()->startOfQuarter(), $now->addQuarter()->endOfQuarter()],
            'this_year' => [$now->copy()->startOfYear(), $now->endOfYear()],
            'last_year' => [$now->copy()->subYear()->startOfYear(), $now->subYear()->endOfYear()],
            'next_year' => [$now->copy()->addYear()->startOfYear(), $now->addYear()->endOfYear()],
            'last_7_days' => [$now->copy()->subDays(7)->startOfDay(), $now->endOfDay()],
            'last_14_days' => [$now->copy()->subDays(14)->startOfDay(), $now->endOfDay()],
            'last_30_days' => [$now->copy()->subDays(30)->startOfDay(), $now->endOfDay()],
            'last_60_days' => [$now->copy()->subDays(60)->startOfDay(), $now->endOfDay()],
            'last_90_days' => [$now->copy()->subDays(90)->startOfDay(), $now->endOfDay()],
            'last_365_days' => [$now->copy()->subDays(365)->startOfDay(), $now->endOfDay()],
            default => throw new InvalidArgumentException('Invalid date type.')
        };

        $period[0] = $period[0]->inAppTimezone();
        $period[1] = $period[1]->inAppTimezone();

        return $period;
    }
}
