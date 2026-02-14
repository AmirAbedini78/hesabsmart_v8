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

use Illuminate\Support\Facades\Log;
use Modules\Core\Support\LogReader;
use Tests\TestCase;

class LogReaderTest extends TestCase
{
    protected function tearDown(): void
    {
        LogReader::glob(null);
        parent::tearDown();
    }

    public function test_it_can_read_log_files(): void
    {
        $reader = new LogReader(['date' => $date = date('Y-m-d')]);
        Log::debug('Test log');

        $logs = $reader->get();

        $this->assertEquals($date, $logs['date']);
        $this->assertArrayHasKey('log_dates', $logs);
        $this->assertArrayHasKey('logs', $logs);
        $this->assertNotNull(collect($logs['logs'])->where('message', 'Test log')->first(), 'The "Test log" was not found in the logs.');
    }

    public function test_it_uses_the_first_log_date_if_no_date_provided(): void
    {
        $reader = new LogReader;
        Log::debug('Test log');

        $logs = $reader->get();

        $this->assertEquals(date('Y-m-d'), $logs['date']);
    }

    public function test_it_can_determine_when_there_are_no_logs_available(): void
    {
        LogReader::glob(storage_path('logs/fake/laravel-*.log'));
        $reader = new LogReader;

        $logs = $reader->get();

        $this->assertFalse($logs['success']);
        $this->assertSame('No logs available', $logs['message']);
        $this->assertCount(0, $logs['log_dates']);
    }

    public function test_it_can_determine_when_there_are_no_logs_available_for_the_given_date(): void
    {
        $reader = new LogReader(['date' => date('Y-m-d', strtotime('+1 year'))]);
        Log::debug('Test log');

        $logs = $reader->get();

        $this->assertFalse($logs['success']);
        $this->assertSame('No log file found for the selected date', $logs['message']);
    }
}
