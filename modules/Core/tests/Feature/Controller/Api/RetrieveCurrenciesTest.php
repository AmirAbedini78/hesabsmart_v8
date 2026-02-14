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

use Akaunting\Money\Currency;
use Tests\TestCase;

class RetrieveCurrenciesTest extends TestCase
{
    public function test_unauthenticated_cannot_access_currency_endpoints(): void
    {
        $this->getJson('/api/currencies')->assertUnauthorized();
    }

    public function test_user_can_fetch_currencies(): void
    {
        $this->signIn();

        $this->getJson('/api/currencies')
            ->assertOk()
            ->assertJson(Currency::getCurrencies());
    }
}
