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

namespace Modules\Deals\Tests\Feature;

use Modules\Deals\Models\Deal;
use Tests\TestCase;

class DealStatusControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_deals_status_endpoints(): void
    {
        $this->getJson('/api/deals/FAKE_ID/status/FAKE_STATUS')->assertUnauthorized();
    }

    public function test_an_authorized_user_can_change_the_deal_status(): void
    {
        $this->signIn();
        $deal = Deal::factory()->create();

        $this->putJson("/api/deals/{$deal->id}/status/won")
            ->assertOk()
            ->assertJson(['status' => 'won']);
    }

    public function test_unauthorized_user_cannot_change_the_deal_status(): void
    {
        $user = $this->asRegularUser()->signIn();
        $deal = Deal::factory()->for($user)->create();

        $this->putJson("/api/deals/{$deal->id}/status/won")
            ->assertForbidden();
    }

    public function test_deal_cant_be_marked_as_lost_when_has_status_won(): void
    {
        $this->signIn();
        $deal = Deal::factory()->won()->create();

        $this->putJson("/api/deals/{$deal->id}/status/lost")
            ->assertStatusConflict();
    }

    public function test_deal_cant_be_marked_as_won_when_has_status_lost(): void
    {
        $this->signIn();
        $deal = Deal::factory()->lost()->create();

        $this->putJson("/api/deals/{$deal->id}/status/won")
            ->assertStatusConflict();
    }
}
