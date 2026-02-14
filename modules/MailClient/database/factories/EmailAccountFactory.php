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

namespace Modules\MailClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Common\Synchronization\SyncState;
use Modules\Core\Database\Factories\OAuthAccountFactory;
use Modules\MailClient\Client\ConnectionType;
use Modules\MailClient\Models\EmailAccount;
use Modules\Users\Models\User;

class EmailAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = EmailAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'connection_type' => ConnectionType::Imap,
            'requires_auth' => false,
            'initial_sync_from' => now(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the account requires authentication
     */
    public function requiresAuth(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'requires_auth' => true,
            ];
        });
    }

    /**
     * Indicate that the account is personal
     */
    public function personal(?User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user ?: User::factory(),
            ];
        });
    }

    /**
     * Indicate that the account is shared
     */
    public function shared(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => null,
            ];
        });
    }

    /**
     * Indicate that the account is of type IMAP
     */
    public function imap(array $overwrite = []): static
    {
        return $this->state(function (array $attributes) use ($overwrite) {
            return array_merge([
                'password' => 'test',
                'imap_server' => 'imap.example.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'smtp_server' => 'smtp.example.com',
                'smtp_port' => 465,
                'smtp_encryption' => 'ssl',
                'validate_cert' => false,
            ], $overwrite, ['connection_type' => ConnectionType::Imap]);
        });
    }

    /**
     * Indicate that the account is of type Gmail
     */
    public function gmail(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'connection_type' => ConnectionType::Gmail,
            ];
        });
    }

    /**
     * Indicate that the account is of type Outlook
     */
    public function outlook(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'connection_type' => ConnectionType::Outlook,
            ];
        });
    }

    /**
     * Indicate that the account sync is disabled
     */
    public function syncDisabled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'sync_state' => SyncState::DISABLED,
            ];
        });
    }

    /**
     * Indicate that the account sync is stopped
     */
    public function syncStopped(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'sync_state' => SyncState::STOPPED,
            ];
        });
    }

    /**
     * Add OAuth account to the email account.
     */
    public function hasOAuth(OAuthAccountFactory $OAuthAccountFactory): static
    {
        return $this->afterCreating(function (EmailAccount $account) use ($OAuthAccountFactory) {
            $account->access_token_id = $OAuthAccountFactory->create()->getKey();
            $account->save();
        });
    }
}
