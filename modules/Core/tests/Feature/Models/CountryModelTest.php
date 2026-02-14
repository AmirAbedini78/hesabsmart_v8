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

namespace Modules\Core\Tests\Feature\Models;

use Illuminate\Support\Facades\Lang;
use Modules\Core\Database\Seeders\CountriesSeeder;
use Modules\Core\Models\Country;
use Tests\TestCase;

class CountryModelTest extends TestCase
{
    public function test_country_can_be_translated_with_custom_group(): void
    {
        $this->seed(CountriesSeeder::class);
        $model = Country::first();

        Lang::addLines(['custom.country.'.$model->id => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_country_can_be_translated_with_lang_key(): void
    {
        $this->seed(CountriesSeeder::class);
        $model = Country::first()->forceFill(['name' => 'custom.country.some']);

        Lang::addLines(['custom.country.some' => 'Changed'], 'en');

        $this->assertSame('Changed', $model->name);
    }

    public function test_it_uses_database_name_when_no_custom_trans_available(): void
    {
        $this->seed(CountriesSeeder::class);
        $model = Country::first()->forceFill(['name' => 'Database Name']);

        $this->assertSame('Database Name', $model->name);
    }

    public function test_it_can_retrieve_a_list_of_countries(): void
    {
        $this->assertIsArray(Country::list());
        $this->assertNotEmpty(Country::list());
        $this->assertCount(249, Country::list());
    }
}
