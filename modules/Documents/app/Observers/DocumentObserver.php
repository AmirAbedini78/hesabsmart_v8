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

namespace Modules\Documents\Observers;

use Modules\Documents\Models\Document;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        $document->addActivity([
            'lang' => [
                'key' => 'documents::document.activity.created',
                'attrs' => [
                    // for unit tests
                    'user' => auth()->user()?->name,
                ],
            ],
        ]);
    }

    /**
     * Handle the Document "deleting" event.
     */
    public function deleting(Document $document): void
    {
        if ($document->isForceDeleting()) {
            $document->purge();
        }
    }
}
