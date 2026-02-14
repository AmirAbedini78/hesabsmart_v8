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

use Illuminate\Support\Facades\DB;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentTemplate;
use Modules\Updater\UpdatePatcher;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        Document::cursor()->each(function ($document) {
            $document->fill([
                'content' => $this->performContentReplacements($document->getAttributes()['content']),
            ])->save();
        });

        DocumentTemplate::cursor()->each(function ($template) {
            $template->fill([
                'content' => $this->performContentReplacements($template->getAttributes()['content']),
            ])->save();
        });
    }

    public function shouldRun(): bool
    {
        return $this->usingOldDocumentPlaceholders() || $this->usingOldTemplatePlaceholders();
    }

    protected function usingOldDocumentPlaceholders(): bool
    {
        return $this->scopeWithOldPlaceholders(DB::table('documents'))->count() > 0;
    }

    protected function usingOldTemplatePlaceholders(): bool
    {
        return $this->scopeWithOldPlaceholders(DB::table('document_templates'))->count() > 0;
    }

    protected function scopeWithOldPlaceholders($query)
    {
        return $query->where('content', 'like', '%document_%')
            ->orWhere('content', 'like', '%brand_%')
            ->orWhere('content', 'like', '%deal_%')
            ->orWhere('content', 'like', '%contact_%')
            ->orWhere('content', 'like', '%company_%');
    }

    protected function performContentReplacements($content)
    {
        if (! $content) {
            return $content;
        }

        // company_name was duplicated, only worked for the main company not for the resource company
        // we will keep it that way as the user probably saw a replacement of the main company name
        $content = str_replace(['{{ company_name', '{{company_name'], '{{ tmp_company_name', $content);

        $content = str_replace(
            ['{{document_created_date', '{{ document_created_date'],
            '{{ document.created_at',
            $content,
        );

        $content = str_replace(
            ['{{ brand_', '{{ document_', '{{ deal_', '{{ contact_', '{{ company_'],
            ['{{ brand.', '{{ document.', '{{ deal.', '{{ contact.', '{{ company.'],
            $content,
        );

        $content = str_replace(
            ['{{brand_', '{{document_', '{{deal_', '{{contact_', '{{company_'],
            ['{{ brand.', '{{ document.', '{{ deal.', '{{ contact.', '{{ company.'],
            $content,
        );

        $content = str_replace('{{ tmp_company_name', '{{ company_name', $content);

        return $content;
    }
};
