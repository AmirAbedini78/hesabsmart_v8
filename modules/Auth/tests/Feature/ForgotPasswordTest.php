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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Modules\Users\Mail\ResetPassword as MailResetPassword;
use Modules\Users\Models\User;
use Modules\Users\Notifications\ResetPassword;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    public function test_user_receives_an_email_with_password_reset_link(): void
    {
        Notification::fake();

        $user = $this->createUser();

        $this->post('/password/email', [
            'email' => $user->email,
        ]);

        $this->assertNotNull($token = DB::table(config('auth.passwords.users.table'))->first());

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function (ResetPassword $notification, array $channels) use ($token, $user) {
                return count($channels) === 1 &&
                    $channels[0] === 'mail' &&
                    Hash::check($notification->token, $token->token) === true &&
                    $notification->toMail($user) instanceof MailResetPassword;
            }
        );
    }

    public function test_reset_password_notification_is_not_user_configureable(): void
    {
        $this->assertFalse(ResetPassword::$configurable);
    }

    public function test_user_does_not_receive_email_when_not_exists(): void
    {
        Notification::fake();

        $response = $this->from('/password/email')->post('/password/email', [
            'email' => 'nobody@example.com',
        ]);

        $response->assertRedirect('/password/email');
        $response->assertSessionHasErrors('email');

        Notification::assertNotSentTo(User::factory()->make(['email' => 'nobody@example.com']), ResetPassword::class);
    }

    public function test_email_is_required_to_request_password_reset(): void
    {
        $response = $this->from('/password/email')->post('/password/email', []);
        $response->assertRedirect('/password/email');
        $response->assertSessionHasErrors('email');
    }

    public function test_it_validate_the_password_reset_email(): void
    {
        $response = $this->from('/password/email')->post('/password/email', [
            'email' => 'invalid-email',
        ]);

        $response->assertRedirect('/password/email');
        $response->assertSessionHasErrors('email');
    }

    public function test_password_reset_can_be_disabled(): void
    {
        settings()->set('disable_password_forgot', true)->save();

        $this->get('/password/reset')->assertNotFound();
        $this->post('/password/email')->assertNotFound();
    }
}
