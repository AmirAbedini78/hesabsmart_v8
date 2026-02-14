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

namespace Modules\Core\Card;

use Modules\Core\Models\Dashboard;
use Modules\Users\Models\User;

class DashboardService
{
    /**
     * Create new dashboard for the given user.
     */
    public function create(array $attributes, int $userId): Dashboard
    {
        $attributes['user_id'] = $userId;
        $attributes['is_default'] ??= false;
        $attributes['cards'] ??= Dashboard::defaultCards(User::find($userId));

        $dashboard = new Dashboard;
        $dashboard->fill($attributes)->save();

        if ($dashboard->is_default) {
            Dashboard::where('id', '!=', $dashboard->id)->update(['is_default' => false]);
        }

        return $dashboard;
    }

    /**
     * Create default dashboard for the given user.
     */
    public function createDefaultFor(User $user): Dashboard
    {
        return $this->create(['name' => __('core::app.application_dashboard'), 'is_default' => true], $user->id);
    }
}
