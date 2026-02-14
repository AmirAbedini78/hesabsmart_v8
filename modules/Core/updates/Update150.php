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

use App\ToModuleMigrator;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Updater\UpdatePatcher;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->longText('data')->nullable()->change();
        });

        DB::table('filters')
            ->where('rules', 'like', '%"operator":"was"%')
            ->update([
                'rules' => DB::raw("REPLACE(rules, '\"operator\":\"was\"', '\"operator\":\"is\"')"),
            ]);

        // Convert filters to views.
        $filters = DB::table('filters')->get();

        foreach ($filters as $filter) {
            if (! empty($filter->flag) || $filter->identifier === 'emails') {
                continue;
            }

            // Create and populate the DataView
            $dataView = [
                'name' => $filter->name,
                'identifier' => $filter->identifier,
                'user_id' => $filter->user_id,
                'is_shared' => $filter->is_shared ? 1 : 0,
                'rules' => Json::encode([Json::decode($filter->rules)]),
                'flag' => $filter->flag,
                'config' => Json::encode([]),
                'created_at' => $filter->created_at,
                'updated_at' => $filter->updated_at,
            ];

            DB::table('data_views')->insert($dataView);
        }

        DB::table('filters')->delete();

        ToModuleMigrator::make('auth')->migrateLanguageFiles(['auth.php']);
    }

    public function shouldRun(): bool
    {
        return Schema::hasTable('filters') && DB::table('filters')->count() > 0;
    }
};
