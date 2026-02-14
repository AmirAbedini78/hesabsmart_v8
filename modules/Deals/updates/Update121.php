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
        if (is_null($this->getDealsTableNameIndexName())) {
            Schema::table('deals', function (Blueprint $table) {
                $table->index('name');
            });
        }
    }

    public function shouldRun(): bool
    {
        return is_null($this->getDealsTableNameIndexName());
    }

    protected function getDealsTableNameIndexName()
    {
        foreach ($this->getDealsTableNameIndexes() as $index) {
            if (str_ends_with($index['name'], 'name_index') && $index['type'] == 'btree') {
                return $index['name'];
            }
        }
    }

    protected function getDealsTableNameIndexes()
    {
        return $this->getColumnIndexes('deals', 'name');
    }
};
