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

use Modules\Activities\Models\Activity;
use Modules\Calls\Models\Call;
use Modules\Contacts\Enums\PhoneType;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\Phone;
use Modules\Contacts\Models\Source;
use Modules\Core\Database\Seeders\CountriesSeeder;
use Modules\Core\Models\Country;
use Modules\Deals\Models\Deal;
use Modules\Notes\Models\Note;
use Modules\Users\Models\User;
use Tests\TestCase;

class ContactModelTest extends TestCase
{
    public function test_when_contact_created_by_not_provided_uses_current_user_id(): void
    {
        $user = $this->signIn();

        $contact = Contact::factory(['created_by' => null])->create();

        $this->assertEquals($contact->created_by, $user->id);
    }

    public function test_contact_created_by_can_be_provided(): void
    {
        $user = $this->createUser();

        $contact = Contact::factory()->for($user, 'creator')->create();

        $this->assertEquals($contact->created_by, $user->id);
    }

    public function test_contact_has_country(): void
    {
        $this->seed(CountriesSeeder::class);

        $contact = Contact::factory()->for(Country::first())->create();

        $this->assertInstanceOf(Country::class, $contact->country);
    }

    public function test_contact_has_user(): void
    {
        $contact = Contact::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $contact->user);
    }

    public function test_contact_has_source(): void
    {
        $contact = Contact::factory()->for(Source::factory())->create();

        $this->assertInstanceOf(Source::class, $contact->source);
    }

    public function test_contact_has_deals(): void
    {
        $contact = Contact::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $contact->deals);
    }

    public function test_contact_has_phones(): void
    {
        $this->seed(CountriesSeeder::class);

        $contact = Contact::factory()->has(Phone::factory()->count(2))->create();

        $this->assertCount(2, $contact->phones);
    }

    public function test_contact_has_calls(): void
    {
        $contact = Contact::factory()->has(Call::factory()->count(2))->create();

        $this->assertCount(2, $contact->calls);
    }

    public function test_contact_has_notes(): void
    {
        $contact = Contact::factory()->has(Note::factory()->count(2))->create();

        $this->assertCount(2, $contact->notes);
    }

    public function test_contact_has_companies(): void
    {
        $contact = Contact::factory()->has(Company::factory()->count(2))->create();

        $this->assertCount(2, $contact->companies);
    }

    public function test_contact_has_activities(): void
    {
        $contact = Contact::factory()->has(Activity::factory()->count(2))->create();

        $this->assertCount(2, $contact->activities);
    }

    public function test_it_can_find_contact_by_phone(): void
    {
        $this->seed(CountriesSeeder::class);

        Contact::factory()->has(Phone::factory()->state(function ($attributes) {
            return ['number' => '255-255-255'];
        }))->create();

        $contact = Contact::byPhone('255-255-255')->first();

        $this->assertNotNull($contact);

        Contact::factory()->has(Phone::factory()->state(function ($attributes) {
            return ['number' => '255-255-244', 'type' => PhoneType::work];
        }))->create();

        $contact = Contact::byPhone('255-255-244', PhoneType::work);

        $this->assertNotNull($contact);
    }
}
