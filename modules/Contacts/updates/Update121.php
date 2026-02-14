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
        if (is_null($this->getContactsTableFirstNameLastNameIndexName())) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->index(['first_name', 'last_name']);
            });
        }
    }

    public function shouldRun(): bool
    {
        return is_null($this->getContactsTableFirstNameLastNameIndexName());
    }

    protected function getContactsTableFirstNameLastNameIndexName()
    {
        foreach ($this->getContactsTableFirstNameAndLastNameIndexes() as $index) {
            if (str_ends_with($index['name'], 'first_name_last_name_index') && $index['type'] == 'btree') {
                return $index['name'];
            }
        }
    }

    protected function getContactsTableFirstNameAndLastNameIndexes()
    {
        return $this->getColumnIndexes('contacts', 'first_name');
    }
};
