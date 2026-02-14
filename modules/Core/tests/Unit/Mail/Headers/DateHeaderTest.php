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

namespace Modules\Core\Tests\Unit\Mail\Headers;

use Carbon\Carbon;
use Modules\Core\Common\Mail\Headers\DateHeader;
use PHPUnit\Framework\TestCase;

class DateHeaderTest extends TestCase
{
    protected $tz = 'UTC';

    public function test_date_header_is_converted_to_carbon_instance(): void
    {
        $header = new DateHeader('date', '2022-01-20 15:00:00');

        $this->assertInstanceOf(Carbon::class, $header->getValue($this->tz));
        $this->assertSame($this->tz, (string) $header->getValue($this->tz)->timezone);

        $header = new DateHeader('date', '2022-01-20T15:00:00+01:00');
        $this->assertSame('2022-01-20 14:00:00', $header->getValue($this->tz)->format('Y-m-d H:i:s'));
        $this->assertSame($this->tz, (string) $header->getValue($this->tz)->timezone);
    }

    public function test_date_header_value_is_null_when_header_has_no_date(): void
    {
        $header = new DateHeader('date', null);

        $this->assertNull($header->getValue($this->tz));
    }
}
