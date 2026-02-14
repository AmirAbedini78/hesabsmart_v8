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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Common\Synchronization\SyncState;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('synchronizations', function (Blueprint $table) {
            $table->uuid('id');
            $table->morphs('synchronizable');
            $table->string('token')->nullable();
            $table->string('resource_id')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->datetime('last_synchronized_at');
            $table->datetime('start_sync_from');
            $table->string('sync_state', 30)->default(SyncState::ENABLED->value);
            $table->text('sync_state_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('synchronizations');
    }
};
