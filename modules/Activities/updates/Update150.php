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

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Updater\UpdatePatcher;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        Schema::table('activity_calendar_sync', function (Blueprint $table) {
            $table->dropIndex(['icaluid']);
        });

        Schema::table('activity_calendar_sync', function (Blueprint $table) {
            $table->text('icaluid')->change();
        });
    }

    public function shouldRun(): bool
    {
        return Schema::hasIndex(
            'activity_calendar_sync',
            config('database.connections.mysql.prefix').'activity_calendar_sync_icaluid_index'
        );
    }
};
