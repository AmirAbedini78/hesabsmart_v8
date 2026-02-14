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

namespace Modules\Core\Common;

use Modules\Core\Facades\Innoclapps;
use Modules\Core\Resource\Resource;

class PermissionsProvider
{
    public function __invoke(Resource $resource)
    {
        Innoclapps::permissions(function ($manager) use ($resource) {
            $group = ['name' => $resource->name(), 'as' => $resource->label()];

            $manager->group($group, function ($manager) use ($resource) {
                $manager->view('view', [
                    'as' => __('core::role.capabilities.view'),
                    'permissions' => [
                        'view own '.$resource->name() => __('core::role.capabilities.owning_only'),
                        'view all '.$resource->name() => __('core::role.capabilities.all', ['resourceName' => $resource->label()]),
                        'view team '.$resource->name() => __('users::team.capabilities.team_only'),
                    ],
                ]);

                $manager->view('edit', [
                    'as' => __('core::role.capabilities.edit'),
                    'permissions' => [
                        'edit own '.$resource->name() => __('core::role.capabilities.owning_only'),
                        'edit all '.$resource->name() => __('core::role.capabilities.all', ['resourceName' => $resource->label()]),
                        'edit team '.$resource->name() => __('users::team.capabilities.team_only'),
                    ],
                ]);

                $manager->view('delete', [
                    'as' => __('core::role.capabilities.delete'),
                    'revokeable' => true,
                    'permissions' => [
                        'delete own '.$resource->name() => __('core::role.capabilities.owning_only'),
                        'delete any '.$resource->singularName() => __('core::role.capabilities.all', ['resourceName' => $resource->label()]),
                        'delete team '.$resource->name() => __('users::team.capabilities.team_only'),
                    ],
                ]);

                $manager->view('bulk_delete', [
                    'permissions' => [
                        'bulk delete '.$resource->name() => __('core::role.capabilities.bulk_delete'),
                    ],
                ]);
            });
        });
    }
}
