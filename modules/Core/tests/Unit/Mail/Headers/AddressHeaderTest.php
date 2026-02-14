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

use Modules\Core\Common\Mail\Headers\AddressHeader;
use PHPUnit\Framework\TestCase;

class AddressHeaderTest extends TestCase
{
    public function test_address_header_has_address(): void
    {
        $header = new AddressHeader('to', 'email@example.com');

        $this->assertSame('email@example.com', $header->getAddress());
    }

    public function test_address_header_has_person_name(): void
    {
        $header = new AddressHeader('to', 'email@example.com', 'Marjan');

        $this->assertSame('Marjan', $header->getPersonName());
    }

    public function test_address_header_has_addresses(): void
    {
        $header = new AddressHeader('to', 'email@example.com', 'Marjan');

        $this->assertCount(1, $header->getAll());
    }

    public function test_address_header_is_parsed_correctly(): void
    {
        $header = new AddressHeader('to', 'John <john@example.com>');

        $this->assertSame('John', $header->getPersonName());
        $this->assertSame('john@example.com', $header->getAddress());

        $header = new AddressHeader('to', 'John <john@example.com>, Jack <jack@example.com>');

        $this->assertCount(2, $header->getAll());
        $this->assertSame('John', $header->getAll()[0]['name']);
        $this->assertSame('john@example.com', $header->getAll()[0]['address']);
        $this->assertSame('Jack', $header->getAll()[1]['name']);
        $this->assertSame('jack@example.com', $header->getAll()[1]['address']);

        $header = new AddressHeader('to', [
            'jack@example.com' => 'Jack',
            'john@example.com',
        ]);

        $this->assertCount(2, $header->getAll());

        $this->assertSame('Jack', $header->getAll()[0]['name']);
        $this->assertSame('jack@example.com', $header->getAll()[0]['address']);

        $this->assertSame('john@example.com', $header->getAll()[1]['address']);
        $this->assertSame('john@example.com', $header->getAll()[1]['name']);
    }

    public function test_address_header_arrayable_returns_all_addresses(): void
    {
        $header = new AddressHeader('to', 'John <john@example.com>, Jack <jack@example.com>');

        $this->assertCount(2, $header->toArray());
    }
}
