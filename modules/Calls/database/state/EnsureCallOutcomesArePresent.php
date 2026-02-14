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

namespace Modules\Calls\Database\State;

use Modules\Calls\Models\CallOutcome;

class EnsureCallOutcomesArePresent
{
    public array $outcomes = [
        'No Answer' => '#f43f5e',
        'Busy' => '#f43f5e',
        'Wrong Number' => '#8898aa',
        'Unavailable' => '#8898aa',
        'Left voice message' => '#ffd600',
        'Moved conversation forward' => '#a3e635',
    ];

    public function __invoke(): void
    {
        if ($this->present()) {
            return;
        }

        foreach ($this->outcomes as $name => $color) {
            CallOutcome::create(['name' => $name, 'swatch_color' => $color]);
        }
    }

    private function present(): bool
    {
        return CallOutcome::query()->count() > 0;
    }
}
