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

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_user_can_view_the_login_form(): void
    {
        $response = $this->get('/login');
        $response->assertSuccessful();
        $response->assertViewIs('auth::login');
    }

    public function test_user_cannot_view_the_login_form_when_authenticated(): void
    {
        $user = $this->signIn();
        $response = $this->get('/login');

        $response->assertRedirect($user->landingPage());
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = $this->withUserAttrs([
            'password' => bcrypt($password = 'password'),
        ])->createUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect($user->landingPage());

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = $this->createUser();

        $response = $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'invalid-password',
            ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));

        $this->assertGuest();
    }

    public function test_remember_me_functionality(): void
    {
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertRedirect($user->landingPage());

        $response->assertCookie(Auth::guard()->getRecallerName(), vsprintf('%s|%s|%s', [
            $user->id,
            $user->getRememberToken(),
            $user->password,
        ]));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_make_more_then_give_login_attempts_in_a_minute(): void
    {
        $user = $this->createUser();

        foreach (range(0, 5) as $_) {
            $response = $this->from('/login')->post('/login', [
                'email' => $user->email,
                'password' => 'invalid-password',
            ]);
        }

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');

        $this->assertStringContainsString(
            'Too many login attempts.',
            collect(
                $response
                    ->baseResponse
                    ->getSession()
                    ->get('errors')
                    ->getBag('default')
                    ->get('email')
            )->first()
        );

        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
}
