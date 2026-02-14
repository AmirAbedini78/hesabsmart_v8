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

namespace Modules\Documents\Console\Commands;

use Illuminate\Console\Command;
use Modules\Documents\Models\Document;

class SendScheduledDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the documents which are scheduled for sending.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Document::dueForSending()
            ->get()
            ->each(function (Document $document) {
                try {
                    $document->send();
                } finally {
                    $document->fill(['send_at' => null])->save();
                }
            });
    }
}
