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
        if (is_null($this->getActivitiesTableTitleIndexName())) {
            Schema::table('activities', function (Blueprint $table) {
                $table->index('title');
            });
        }

        if (is_null($this->getActivitiesTableDueDateDueTimeIndexName())) {
            Schema::table('activities', function (Blueprint $table) {
                $table->index(['due_date', 'due_time']);
            });
        }
    }

    public function shouldRun(): bool
    {
        return is_null($this->getActivitiesTableTitleIndexName()) ||
            is_null($this->getActivitiesTableDueDateDueTimeIndexName());
    }

    protected function getActivitiesTableTitleIndexName()
    {
        foreach ($this->getActivitiesTableTitleIndexes() as $index) {
            if (str_ends_with($index['name'], 'title_index') && $index['type'] == 'btree') {
                return $index['name'];
            }
        }
    }

    protected function getActivitiesTableTitleIndexes()
    {
        return $this->getColumnIndexes('activities', 'title');
    }

    protected function getActivitiesTableDueDateDueTimeIndexName()
    {
        foreach ($this->getActivitiesTableDueDateDueTimeIndexes() as $index) {
            if (str_ends_with($index['name'], 'due_date_due_time_index') && $index['type'] == 'btree') {
                return $index['name'];
            }
        }
    }

    protected function getActivitiesTableDueDateDueTimeIndexes()
    {
        return $this->getColumnIndexes('activities', 'due_date');
    }
};
