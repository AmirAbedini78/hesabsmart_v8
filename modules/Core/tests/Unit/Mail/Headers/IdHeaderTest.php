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

use Modules\Core\Common\Mail\Headers\IdHeader;
use PHPUnit\Framework\TestCase;

class IdHeaderTest extends TestCase
{
    public function test_id_header_has_value(): void
    {
        $header = new IdHeader('message-id', '<unique-id@example.com>');

        $this->assertSame('unique-id@example.com', $header->getValue());
    }

    public function test_id_header_is_properly_parsed(): void
    {
        $header = new IdHeader('message-id', '<unique-id@example.com>');

        $this->assertSame('unique-id@example.com', $header->getValue());

        $header = new IdHeader('message-id', '<unique-id@example.com>, <unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', '<unique-id@example.com>,<unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', '<unique-id@example.com>any-separator <unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', '<unique-id@example.com>    <unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', '<unique-id@example.com><unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', ['<unique-id@example.com>', '<unique-id1@example.com>']);
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);
    }
}
