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

namespace Modules\Users\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Users\Models\User;

class UserAvatarService
{
    /**
     * Store the given user avatar.
     */
    public function store(User $user, UploadedFile $file): User
    {
        static::remove($user);

        $user->fill(['avatar' => $file->store('avatars', 'public')])->save();

        return $user;
    }

    /**
     * Delete user avatar
     */
    public static function remove(User $user): void
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
    }
}
