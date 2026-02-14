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

namespace Modules\Users\Support;

use Illuminate\Support\Arr;
use Modules\Users\Models\User;

class TeamCache
{
    protected static ?array $usersTeams = null;

    public static function userManagesAnyTeamsOf(int $userId, int $ofUserId): bool
    {
        static::cacheUserTeams();

        return in_array($userId, static::$usersTeams[$ofUserId]);
    }

    public static function flush(): void
    {
        static::$usersTeams = null;
    }

    protected static function cacheUserTeams(): void
    {
        if (! static::$usersTeams) {
            static::$usersTeams = User::with(['teams' => function ($query) {
                $query->select(['teams.user_id']);
            }])
                ->get(['id'])
                ->mapWithKeys(function (User $user) {
                    return [$user->id => Arr::pluck($user->teams, 'user_id')];
                })->all();
        }
    }
}
