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

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

/** @mixin \Modules\Core\Models\Model */
trait HasAvatar
{
    /**
     * Get Gravatar URL.
     */
    public function getGravatarUrl(?string $email = null, string|int $size = '40'): string
    {
        $email ??= $this->email ?? '';

        return 'https://www.gravatar.com/avatar/'.md5(strtolower($email)).'?s='.$size;
    }

    /**
     * Get the model avatar URL.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            if (is_null($this->avatar)) {
                return $this->getGravatarUrl();
            }

            return $this->uploadedAvatarUrl;
        });
    }

    /**
     * Get the actual uploaded path URL for src image.
     */
    protected function uploadedAvatarUrl(): Attribute
    {
        return Attribute::get(function () {
            if (is_null($this->avatar)) {
                return null;
            }

            return Storage::url($this->avatar);
        });
    }
}
