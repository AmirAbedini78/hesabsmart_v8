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

namespace Modules\WebForms\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Contacts\Models\Phone;

class PhoneMerger
{
    public function merge(Collection|array $oldPhones, array $newPhones): array
    {
        // Create an array where keys are phone numbers for easy lookup
        $mergedPhones = [];

        if ($oldPhones instanceof Collection) {
            $oldPhones = $oldPhones->map(
                fn (Phone $phone) => ['number' => $phone->number, 'type' => $phone->type]
            );
        }

        foreach ($oldPhones as $entry) {
            $mergedPhones[$entry['number']] = $entry;
        }

        foreach ($newPhones as $entry) {
            // If number exists in $mergedPhones, it will replace the old entry
            $mergedPhones[$entry['number']] = $entry; // Replace or add new entry
        }

        return array_values($mergedPhones);
    }
}
