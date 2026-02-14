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

class CompanyModelTest extends TestCase
{
    public function test_when_company_created_by_not_provided_uses_current_user_id(): void
    {
        $user = $this->signIn();

        $company = Company::factory(['created_by' => null])->create();

        $this->assertEquals($company->created_by, $user->id);
    }

    public function test_company_created_by_can_be_provided(): void
    {
        $user = $this->createUser();

        $company = Company::factory()->for($user, 'creator')->create();

        $this->assertEquals($company->created_by, $user->id);
    }

    public function test_company_has_country(): void
    {
        $this->seed(CountriesSeeder::class);

        $company = Company::factory()->for(Country::first())->create();

        $this->assertInstanceOf(Country::class, $company->country);
    }

    public function test_company_has_user(): void
    {
        $company = Company::factory()->for(User::factory())->create();

        $this->assertInstanceOf(User::class, $company->user);
    }

    public function test_company_has_source(): void
    {
        $company = Company::factory()->for(Source::factory())->create();

        $this->assertInstanceOf(Source::class, $company->source);
    }

    public function test_company_has_deals(): void
    {
        $company = Company::factory()->has(Deal::factory()->count(2))->create();

        $this->assertCount(2, $company->deals);
    }

    public function test_company_has_phones(): void
    {
        $this->seed(CountriesSeeder::class);

        $company = Company::factory()->has(Phone::factory()->count(2))->create();

        $this->assertCount(2, $company->phones);
    }

    public function test_company_has_calls(): void
    {
        $company = Company::factory()->has(Call::factory()->count(2))->create();

        $this->assertCount(2, $company->calls);
    }

    public function test_company_has_notes(): void
    {
        $company = Company::factory()->has(Note::factory()->count(2))->create();

        $this->assertCount(2, $company->notes);
    }

    public function test_company_has_contacts(): void
    {
        $company = Company::factory()->has(Contact::factory()->count(2))->create();

        $this->assertCount(2, $company->contacts);
    }

    public function test_company_has_activities(): void
    {
        $company = Company::factory()->has(Activity::factory()->count(2))->create();

        $this->assertCount(2, $company->activities);
    }
}
