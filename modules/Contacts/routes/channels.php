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

use Illuminate\Support\Facades\Broadcast;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Modules\Users\Models\User;

Broadcast::channel('Modules.Contacts.Models.Contact.{contactId}', function (User $user, string $contactId) {
    return $user->can('view', Contact::findOrFail($contactId));
});

Broadcast::channel('Modules.Contacts.Models.Company.{companyId}', function (User $user, string $companyId) {
    return $user->can('view', Company::findOrFail($companyId));
});
