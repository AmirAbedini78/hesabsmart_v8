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

namespace Modules\Contacts\Tests\Feature;

use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\Phone;
use Modules\Core\Database\Seeders\CountriesSeeder;
use Tests\TestCase;

class PhoneModelTest extends TestCase
{
    public function test_it_serializes_the_type_name(): void
    {
        $this->seed(CountriesSeeder::class);
        $contact = Contact::factory()->has(Phone::factory(), 'phones')->create();

        $this->assertArrayHasKey('type', $contact->phones[0]->toArray());
        $this->assertSame($contact->phones[0]->type->name, $contact->phones[0]->toArray()['type']);
    }
}
