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
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Models\CustomField;
use Modules\Updater\UpdatePatcher;

return new class extends UpdatePatcher
{
    protected array $fieldsToDelete = ['ColorSwatches', 'DropdownSelect', 'MailEditor', 'IntroductionField'];

    public function run(): void
    {
        foreach ($this->fieldsToDelete as $filename) {
            if (is_file(module_path('Core', 'App/Fields/'.$filename.'.php'))) {
                unlink(module_path('Core', 'App/Fields/'.$filename.'.php'));
            }
        }

        settings([
            '_last_cron_run' => settings('last_cron_run'),
            'last_cron_run' => null,
        ]);

        // Update old indexes with new.
        $uniqueCustomFields = CustomField::where('is_unique', true)->get();

        foreach ($uniqueCustomFields as $field) {
            $relatedModel = Innoclapps::resourceByName($field->resource_name)->newModel();

            $indexes = $this->getColumnIndexes($relatedModel->getTable(), $field->field_id);

            foreach ($indexes as $index) {
                if ($index['unique'] === true) {
                    Schema::table($relatedModel->getTable(), function (Blueprint $table) use ($index) {
                        $table->dropUnique($index['name']);
                    });

                    Schema::table($relatedModel->getTable(), function (Blueprint $table) use ($field) {
                        $table->unique($field->field_id, $field->uniqueIndexName());
                    });
                }
            }
        }
    }

    public function shouldRun(): bool
    {
        return collect($this->fieldsToDelete)
            ->some(fn ($filename) => is_file(module_path('Core', 'App/Fields/'.$filename.'.php')));
    }
};
