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
        if (! $this->getDocumentsTableTitleColumnIndexName()) {
            Schema::table('documents', function (Blueprint $table) {
                $table->index('title');
            });
        }
    }

    public function shouldRun(): bool
    {
        return ! $this->getDocumentsTableTitleColumnIndexName();
    }

    protected function getDocumentsTableTitleColumnIndexName()
    {
        foreach ($this->getColumnIndexes('documents', 'title') as $index) {
            if (str_ends_with($index['name'], 'title_index') && $index['type'] == 'btree') {
                return $index['name'];
            }
        }
    }
};
