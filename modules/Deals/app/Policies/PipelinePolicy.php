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

namespace Modules\Deals\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Deals\Models\Pipeline;
use Modules\Users\Models\User;

class PipelinePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any pipelines.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the pipeline.
     */
    public function view(User $user, Pipeline $pipeline): bool
    {
        return $pipeline->isVisible($user);
    }

    /**
     * Determine if the given user can create pipeline.
     */
    public function create(User $user): bool
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can update the pipeline.
     */
    public function update(User $user, Pipeline $pipeline): bool
    {
        // Only super admins
        return false;
    }

    /**
     * Determine whether the user can delete the pipeline.
     */
    public function delete(User $user, Pipeline $pipeline): bool
    {
        // Only super admins
        return false;
    }
}
