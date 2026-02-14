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

namespace Modules\Deals\Database\State;

use Modules\Deals\Models\Pipeline;

class EnsureDefaultPipelineIsPresent
{
    public array $stages = [
        [
            'name' => 'Qualified To Buy',
            'win_probability' => 100,
            'display_order' => 1,
        ],
        [
            'name' => 'Contact Made',
            'win_probability' => 100,
            'display_order' => 2,
        ],
        [
            'name' => 'Presentation Scheduled',
            'win_probability' => 100,
            'display_order' => 3,
        ],
        [
            'name' => 'Proposal Made',
            'win_probability' => 100,
            'display_order' => 4,
        ],
        [
            'name' => 'Appointment Scheduled',
            'win_probability' => 100,
            'display_order' => 5,
        ],
    ];

    public function __invoke(): void
    {
        if ($this->present()) {
            return;
        }

        $pipeline = Pipeline::create([
            'name' => 'Sales Pipeline',
            'flag' => 'default',
        ]);

        $pipeline->stages()->createMany($this->stages);
    }

    private function present(): bool
    {
        return Pipeline::query()->where('flag', 'default')->count() > 0;
    }
}
