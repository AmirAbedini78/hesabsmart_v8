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
        if ($this->missingLastContactedAtColumn('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->after('next_activity_id', function (Blueprint $table) {
                    $table->dateTime('last_contacted_at')->nullable()->index();
                });
            });
        }

        if ($this->missingLastContactedAtColumn('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->after('next_activity_id', function (Blueprint $table) {
                    $table->dateTime('last_contacted_at')->nullable()->index();
                });
            });
        }
    }

    public function shouldRun(): bool
    {
        return $this->missingLastContactedAtColumn('contacts') || $this->missingLastContactedAtColumn('companies');
    }

    protected function missingLastContactedAtColumn($table): bool
    {
        return ! Schema::hasColumn($table, 'last_contacted_at');
    }
};
