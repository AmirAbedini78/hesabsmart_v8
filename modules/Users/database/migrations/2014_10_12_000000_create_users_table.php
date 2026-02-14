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

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('timezone');
            $table->string('date_format');
            $table->string('time_format');
            $table->string('locale', 12)->default('en');
            $table->text('mail_signature')->nullable();
            $table->string('default_landing_page')->nullable();
            $table->dateTime('last_active_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->text('notifications_settings')->nullable();
            $table->boolean('super_admin')->default(false);
            $table->boolean('access_api')->default(false);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
