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

namespace Modules\Contacts\Database\State;

use Modules\Contacts\Models\Source;

class EnsureSourcesArePresent
{
    public array $sources = [
        'Organic search',
        'Paid search',
        'Email marketing',
        'Social media',
        'Referrals',
        'Other campaigns',
        'Direct traffic',
        'Offline Source',
        'Paid social',
        'Web Form',
    ];

    public function __invoke(): void
    {
        if ($this->present()) {
            return;
        }

        foreach ($this->sources as $source) {
            Source::create([
                'name' => $source,
                'flag' => $source === 'Web Form' ? 'web-form' : null,
            ]);
        }
    }

    private function present(): bool
    {
        return Source::query()->count() > 0;
    }
}
