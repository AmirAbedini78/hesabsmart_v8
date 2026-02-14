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
use Modules\Documents\Enums\DocumentViewType;
use Modules\Updater\UpdatePatcher;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        if ($this->missingViewTypeDocumentsTableColumn()) {
            Schema::table('documents', function (Blueprint $table) {
                $table->after('content', function ($table) {
                    $table->string('view_type')->default(DocumentViewType::NAV_TOP->value);
                });
            });
        }

        if ($this->missingViewTypeDocumentTemplatesTableColumn()) {
            Schema::table('document_templates', function (Blueprint $table) {
                $table->after('content', function ($table) {
                    $table->string('view_type')->nullable();
                });
            });
        }
    }

    public function shouldRun(): bool
    {
        return $this->missingViewTypeDocumentsTableColumn() ||
            $this->missingViewTypeDocumentTemplatesTableColumn();
    }

    protected function missingViewTypeDocumentsTableColumn(): bool
    {
        return ! Schema::hasColumn('documents', 'view_type');
    }

    protected function missingViewTypeDocumentTemplatesTableColumn(): bool
    {
        return ! Schema::hasColumn('document_templates', 'view_type');
    }
};
