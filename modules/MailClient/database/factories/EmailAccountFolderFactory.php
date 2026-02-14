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
use Illuminate\Support\Str;
use Modules\MailClient\Client\FolderType;
use Modules\MailClient\Models\EmailAccount;
use Modules\MailClient\Models\EmailAccountFolder;

class EmailAccountFolderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = EmailAccountFolder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email_account_id' => EmailAccount::factory(),
            'remote_id' => Str::uuid()->__toString(),
            'type' => FolderType::INBOX,
            'name' => 'INBOX',
            'display_name' => 'INBOX',
            'syncable' => true,
        ];
    }

    public function inbox()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => FolderType::INBOX,
                'name' => 'INBOX',
                'display_name' => 'INBOX',
            ];
        });
    }

    public function trash()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => FolderType::TRASH,
                'name' => 'TRASH',
                'display_name' => 'TRASH',
            ];
        });
    }

    public function sent()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => FolderType::SENT,
                'name' => 'SENT',
                'display_name' => 'SENT',
            ];
        });
    }
}
