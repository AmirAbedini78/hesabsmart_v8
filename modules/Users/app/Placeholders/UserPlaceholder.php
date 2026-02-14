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

namespace Modules\Users\Placeholders;

use Modules\Core\Common\Placeholders\Placeholder;

class UserPlaceholder extends Placeholder
{
    /**
     * Initialize new UserPlaceholder instance.
     *
     * @param  \Closure|mixed  $value
     */
    public function __construct($value = null, string $tag = 'user')
    {
        parent::__construct($tag, $value);

        $this->description(__('users::user.user'));
    }

    /**
     * Format the placeholder
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        return is_a($this->value, \Modules\Users\Models\User::class) ?
            $this->value->name :
            $this->value;
    }
}
