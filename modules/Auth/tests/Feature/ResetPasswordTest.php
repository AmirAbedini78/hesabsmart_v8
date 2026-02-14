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

namespace Modules\Auth\Tests\Feature;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    protected function getValidToken($user)
    {
        return Password::broker()->createToken($user);
    }

    protected function getInvalidToken()
    {
        return 'invalid-token';
    }

    protected function passwordResetGetRoute($token)
    {
        return route('password.reset', $token);
    }

    protected function passwordResetPostRoute()
    {
        return '/password/reset';
    }

    public function test_user_can_view_password_reset_form(): void
    {
        $user = $this->createUser();
        $response = $this->get($this->passwordResetGetRoute($token = $this->getValidToken($user)));
        $response->assertSuccessful();
        $response->assertViewIs('auth::passwords.reset');
        $response->assertViewHas('token', $token);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        Event::fake();

        $user = $this->createUser();

        $response = $this->post($this->passwordResetPostRoute(), [
            'token' => $this->getValidToken($user),
            'email' => $user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect($user->landingPage());
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('new-awesome-password', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(PasswordReset::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    public function test_user_cannot_reset_password_with_invalid_token(): void
    {
        $user = $this->withUserAttrs([
            'password' => Hash::make('old-password'),
        ])->createUser();

        $response = $this->from(
            $this->passwordResetGetRoute($this->getInvalidToken())
        )->post($this->passwordResetPostRoute(), [
            'token' => $this->getInvalidToken(),
            'email' => $user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect($this->passwordResetGetRoute($this->getInvalidToken()));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_user_cannot_reset_password_without_providing_a_new_password(): void
    {
        $user = $this->withUserAttrs([
            'password' => Hash::make('old-password'),
        ])->createUser();

        $response = $this->from(
            $this->passwordResetGetRoute($token = $this->getValidToken($user))
        )->post($this->passwordResetPostRoute(), [
            'token' => $token,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect($this->passwordResetGetRoute($token));
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_user_cannot_reset_password_without_providing_an_email(): void
    {
        $user = $this->withUserAttrs([
            'password' => Hash::make('old-password'),
        ])->createUser();

        $response = $this->from(
            $this->passwordResetGetRoute($token = $this->getValidToken($user))
        )->post($this->passwordResetPostRoute(), [
            'token' => $token,
            'email' => '',
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect($this->passwordResetGetRoute($token));
        $response->assertSessionHasErrors('email');

        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_password_reset_can_be_disabled(): void
    {
        settings()->set('disable_password_forgot', true)->save();

        $user = $this->createUser();

        $this->from(
            $this->passwordResetGetRoute($token = $this->getValidToken($user))
        )->post($this->passwordResetPostRoute(), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->get($this->passwordResetGetRoute($this->getValidToken($user)))->assertNotFound();
    }
}
