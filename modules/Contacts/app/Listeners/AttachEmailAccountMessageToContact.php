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

namespace Modules\Contacts\Listeners;

use Modules\Contacts\Models\Contact;
use Modules\MailClient\Events\EmailAccountMessageCreated;

class AttachEmailAccountMessageToContact
{
    /**
     * When a message is created, try to associate the message with the actual contact if exists in database
     */
    public function handle(EmailAccountMessageCreated $event): void
    {
        $message = $event->message;

        $emails = array_unique(array_filter([
            $message->from?->address,
            ...$message->to->pluck('address')->all(),
            ...$message->cc->pluck('address')->all(),
            // ...$message->bcc->pluck('address')->all(),
        ]));

        if (count($emails) === 0) {
            return;
        }

        $contacts = Contact::whereIn('email', $emails)->get(['id', 'email']);

        foreach ($contacts as $contact) {
            // When receiving an email from for example hannes@company.de it is only directly auto associated to the contact. However in my opinion it should also be auto associated to the company according to the domain company.de
            $emailDomain = substr($contact->email, strpos($contact->email, '@') + 1);
            $relatedCompaniesByEmailDomain = $contact->companies()->where('domain', $emailDomain)->get(['id']);

            // Sync the message to the contact's companies matching email domain
            foreach ($relatedCompaniesByEmailDomain as $company) {
                $company->emails()->syncWithoutDetaching($message->id);
            }

            // Sync the message to the contact
            $contact->emails()->syncWithoutDetaching($message->id);
        }
    }
}
