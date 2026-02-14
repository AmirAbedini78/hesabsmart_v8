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

namespace Modules\Activities\Database\State;

use Modules\Activities\Models\ActivityType;

class EnsureActivityTypesArePresent
{
    public array $types = [
        'Call' => ['#a3e635', 'Phone'],
        'Meeting' => ['#64748b', 'Users'],
        'Task' => ['#ffd600', 'CheckCircle'],
        'Email' => ['#818cf8', 'Mail'],
        'Deadline' => ['#f43f5e', 'Clock'],
    ];

    public function __invoke(): void
    {
        if ($this->present()) {
            return;
        }

        foreach ($this->types as $name => $options) {
            $model = ActivityType::create([
                'name' => $name,
                'swatch_color' => $options[0],
                'icon' => $options[1],
                'flag' => strtolower($name),
            ]);

            if ($model->flag === 'task') {
                $model::setDefault($model->getKey());
            }
        }
    }

    private function present(): bool
    {
        return ActivityType::query()->count() > 0;
    }
}
