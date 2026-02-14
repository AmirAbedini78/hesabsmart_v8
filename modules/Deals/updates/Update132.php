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
        Schema::table('deals', function (Blueprint $table) {
            $table->dropForeign(['web_form_id']);

            $table->foreign('web_form_id')
                ->references('id')
                ->on('web_forms')
                ->nullOnDelete();
        });

        settings(['_patch_deal_web_form_id_applied' => true]);
    }

    public function shouldRun(): bool
    {
        return ! settings('_patch_deal_web_form_id_applied');
    }
};
