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

namespace Modules\Core\Tests\Feature\Controller\Api;

use Modules\Core\Database\Seeders\CountriesSeeder;
use Tests\TestCase;

class RetrieveCountriesTest extends TestCase
{
    public function test_unauthenticated_cannot_access_country_endpoints(): void
    {
        $this->getJson('/api/countries')->assertUnauthorized();
    }

    public function test_user_can_fetch_countries(): void
    {
        $this->signIn();

        $this->seed(CountriesSeeder::class);

        $this->getJson('/api/countries')->assertOk();
    }
}
