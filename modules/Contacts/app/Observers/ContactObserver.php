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

namespace Modules\Contacts\Observers;

use Modules\Activities\Models\Guest;
use Modules\Contacts\Models\Contact;

class ContactObserver
{
    /**
     * Handle the Contact "deleting" event.
     */
    public function deleting(Contact $contact): void
    {
        if ($contact->isForceDeleting()) {
            $contact->purge();
        } else {
            $contact->guests()->delete();
        }
    }

    /**
     * Handle the Contact "restored" event.
     */
    public function restored(Contact $contact)
    {
        $contact->guests()->onlyTrashed()->get()->each(function (Guest $guest) {
            $guest->restore();
        });
    }
}
