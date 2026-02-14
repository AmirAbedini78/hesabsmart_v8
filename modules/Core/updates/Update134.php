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

use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;
use Modules\Updater\UpdatePatcher;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        if ($this->usingOldPermissionGuard()) {
            Permission::where('guard_name', 'api')->update(['guard_name' => 'sanctum']);
            Role::where('guard_name', 'api')->update(['guard_name' => 'sanctum']);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    public function shouldRun(): bool
    {
        return $this->usingOldPermissionGuard();
    }

    protected function usingOldPermissionGuard(): bool
    {
        return Permission::where('guard_name', 'api')->exists() || Role::where('guard_name', 'api')->exists();
    }
};
