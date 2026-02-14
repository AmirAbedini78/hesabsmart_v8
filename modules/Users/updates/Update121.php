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

use Illuminate\Support\Facades\Schema;
use Modules\Updater\UpdatePatcher;
use Modules\Users\Models\User;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        if ($this->missingNotificationsSettingsColumn()) {
            Schema::table('users', function ($table) {
                $table->after('avatar', function ($table) {
                    $table->text('notifications_settings')->nullable();
                });
            });

            User::get()->each(function ($user) {
                $user->notifications_settings = $user->getMeta('notification-settings') ?: [];
                $user->save();
            });
        }
    }

    public function shouldRun(): bool
    {
        return $this->missingNotificationsSettingsColumn();
    }

    protected function missingNotificationsSettingsColumn(): bool
    {
        return ! Schema::hasColumn('users', 'notifications_settings');
    }
};
