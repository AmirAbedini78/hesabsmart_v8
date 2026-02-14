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

namespace Modules\Core\Tests\Feature\Macros;

use Illuminate\Support\Carbon;
use Tests\Fixtures\Event;
use Tests\TestCase;

class QueryDateRangeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2022-01-01');
    }

    public function test_it_queries_for_today(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2022-01-01 00:00:00' and '2022-01-01 23:59:59'",
            Event::whereDateRange('created_at', 'today')->toRawSql()
        );
    }

    public function test_it_queries_for_yesterday(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-12-31 00:00:00' and '2021-12-31 23:59:59'",
            Event::whereDateRange('created_at', 'yesterday')->toRawSql()
        );
    }

    public function test_it_queries_for_next_day(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2022-01-02 00:00:00' and '2022-01-02 23:59:59'",
            Event::whereDateRange('created_at', 'next_day')->toRawSql()
        );
    }

    public function test_it_queries_for_this_week(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-12-27 00:00:00' and '2022-01-02 23:59:59'",
            Event::whereDateRange('created_at', 'this_week')->toRawSql()
        );
    }

    public function test_it_queries_for_last_week(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-12-20 00:00:00' and '2021-12-26 23:59:59'",
            Event::whereDateRange('created_at', 'last_week')->toRawSql()
        );
    }

    public function test_it_queries_for_next_week(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2022-01-03 00:00:00' and '2022-01-09 23:59:59'",
            Event::whereDateRange('created_at', 'next_week')->toRawSql()
        );
    }

    public function test_it_queries_for_this_month(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2022-01-01 00:00:00' and '2022-01-31 23:59:59'",
            Event::whereDateRange('created_at', 'this_month')->toRawSql()
        );
    }

    public function test_it_queries_for_last_month(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-12-01 00:00:00' and '2021-12-31 23:59:59'",
            Event::whereDateRange('created_at', 'last_month')->toRawSql()
        );
    }

    public function test_it_queries_for_next_month(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2022-02-01 00:00:00' and '2022-02-28 23:59:59'",
            Event::whereDateRange('created_at', 'next_month')->toRawSql()
        );
    }

    public function test_it_queries_for_this_quarter(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2022-01-01 00:00:00' and '2022-03-31 23:59:59'",
            Event::whereDateRange('created_at', 'this_quarter')->toRawSql()
        );
    }

    public function test_it_queries_for_last_quarter(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-10-01 00:00:00' and '2021-12-31 23:59:59'",
            Event::whereDateRange('created_at', 'last_quarter')->toRawSql()
        );
    }

    public function test_it_queries_for_next_quarter(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2022-04-01 00:00:00' and '2022-06-30 23:59:59'",
            Event::whereDateRange('created_at', 'next_quarter')->toRawSql()
        );
    }

    public function test_it_queries_for_this_year(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2022-01-01 00:00:00' and '2022-12-31 23:59:59'",
            Event::whereDateRange('created_at', 'this_year')->toRawSql()
        );
    }

    public function test_it_queries_for_last_year(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-01-01 00:00:00' and '2021-12-31 23:59:59'",
            Event::whereDateRange('created_at', 'last_year')->toRawSql()
        );
    }

    public function test_it_queries_for_next_year(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2023-01-01 00:00:00' and '2023-12-31 23:59:59'",
            Event::whereDateRange('created_at', 'next_year')->toRawSql()
        );
    }

    public function test_it_queries_for_last_7_days(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-12-25 00:00:00' and '2022-01-01 23:59:59'",
            Event::whereDateRange('created_at', 'last_7_days')->toRawSql()
        );
    }

    public function test_it_queries_for_last_14_days(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-12-18 00:00:00' and '2022-01-01 23:59:59'",
            Event::whereDateRange('created_at', 'last_14_days')->toRawSql()
        );
    }

    public function test_it_queries_for_last_30_days(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-12-02 00:00:00' and '2022-01-01 23:59:59'",
            Event::whereDateRange('created_at', 'last_30_days')->toRawSql()
        );
    }

    public function test_it_queries_for_last_60_days(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-11-02 00:00:00' and '2022-01-01 23:59:59'",
            Event::whereDateRange('created_at', 'last_60_days')->toRawSql()
        );
    }

    public function test_it_queries_for_last_90_days(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-10-03 00:00:00' and '2022-01-01 23:59:59'",
            Event::whereDateRange('created_at', 'last_90_days')->toRawSql()
        );
    }

    public function test_it_queries_for_last_365_days(): void
    {
        $this->assertStringContainsString(
            "where \"created_at\" between '2021-01-01 00:00:00' and '2022-01-01 23:59:59'",
            Event::whereDateRange('created_at', 'last_365_days')->toRawSql()
        );
    }

    public function test_it_handles_invalid_date_range(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Event::whereDateRange('created_at', 'dummy');
    }
}
