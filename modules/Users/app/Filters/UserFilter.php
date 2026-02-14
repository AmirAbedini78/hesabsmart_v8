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

namespace Modules\Users\Filters;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Core\Filters\Select;
use Modules\Users\Models\User;

class UserFilter extends Select
{
    /**
     * Initialize new UserFilter instance.
     */
    public function __construct(?string $label = null, ?string $field = null)
    {
        parent::__construct($field ?: 'user_id', $label ?: __('users::user.user'));

        $this->valueKey('id')->labelKey('name');
    }

    /**
     * Get the options to be used in quick filter.
     */
    public function getQuickFilterOptions(): array
    {
        $options = parent::getQuickFilterOptions();

        $options[] = [
            'separator' => true,
        ];

        $options[] = [
            'value' => 'unassigned', // dummy value
            'label' => __('users::user.filters.no_user', ['label' => $this->label]),
            'operator' => 'is_null',
            'bold' => true,
        ];

        return $options;
    }

    /**
     * Provides the User filter options.
     */
    public function resolveOptions(): array
    {
        // The user filter is the most used field in the APP,
        // in this case we will make sure to cache them in an array.
        return Cache::store('array')->rememberForever('user-filter-options', function () {
            return User::select([$this->valueKey, $this->labelKey])
                ->orderBy($this->labelKey)
                ->get()
                ->map(function ($user) {
                    if ($user->is(Auth::user())) {
                        return [
                            'id' => 'me',
                            'name' => __('core::filters.me'),
                        ];
                    }

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                    ];
                })
                ->all();
        });
    }

    /**
     * Prepare the query value.
     */
    public function prepareValue($value)
    {
        return $value === 'me' ? Auth::id() : $value;
    }
}
