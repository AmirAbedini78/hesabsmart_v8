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

namespace Modules\Users\Tests\Feature;

use Tests\TestCase;

class PersonalAccessTokenControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_personal_access_tokens_endpoints(): void
    {
        $token = $this->createUser()->createToken('Zapier');

        $this->getJson('/api/personal-access-tokens')->assertUnauthorized();
        $this->postJson('/api/personal-access-tokens')->assertUnauthorized();
        $this->deleteJson('/api/personal-access-tokens/'.$token->accessToken->id)->assertUnauthorized();
    }

    public function test_user_can_create_personal_access_token(): void
    {
        $this->signIn();

        $this->postJson('/api/personal-access-tokens', [
            'name' => 'Zapier',
        ])->assertCreated();

        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'Zapier']);
    }

    public function test_personal_access_token_requires_name(): void
    {
        $this->signIn();

        $this->postJson('/api/personal-access-tokens', [
            'name' => '',
        ])->assertJsonValidationErrors('name');
    }

    public function test_plain_access_token_is_returned_in_the_response(): void
    {
        $this->signIn();

        $response = $this->postJson('/api/personal-access-tokens', [
            'name' => 'Zapier',
        ]);

        $this->assertTrue(property_exists($response->getData(), 'plainTextToken'));
    }

    public function test_personal_access_token_can_be_retrieved(): void
    {
        $user = $this->signIn();
        $user->createToken('Zapier');
        $user->createToken('Concord API');

        $this->getJson('/api/personal-access-tokens')->assertJsonCount(2);
    }

    public function test_user_can_delete_personal_access_token(): void
    {
        $user = $this->signIn();
        $token = $user->createToken('Zapier');

        $this->deleteJson('/api/personal-access-tokens/'.$token->accessToken->id);
        $this->assertDatabaseMissing('personal_access_tokens', ['name' => 'Zapier']);
    }

    public function test_user_can_delete_own_personal_access_token_only(): void
    {
        $user = $this->signIn();
        $user2 = $this->asRegularUser()->createUser();
        $user->createToken('Zapier');
        $token2 = $user2->createToken('Zapier for User 2');

        $this->deleteJson('/api/personal-access-tokens/'.$token2->accessToken->id)->assertNotFound();
    }

    public function test404ErrorIsThrownWhenDeletingTokenThatNotExist()
    {
        $this->signIn();

        $this->deleteJson('/api/personal-access-tokens/DUMMY_ID')->assertNotFound();
    }
}
