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

return new class extends UpdatePatcher
{
    public function run(): void
    {
        if ($this->missingDefaultLandingPageColumn()) {
            Schema::table('users', function ($table) {
                $table->after('mail_signature', function ($table) {
                    $table->string('default_landing_page')->nullable();
                });
            });
        }
    }

    public function shouldRun(): bool
    {
        return $this->missingDefaultLandingPageColumn();
    }

    protected function missingDefaultLandingPageColumn(): bool
    {
        return ! Schema::hasColumn('users', 'default_landing_page');
    }
};
