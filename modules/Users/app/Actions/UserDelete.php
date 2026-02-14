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

namespace Modules\Users\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Actions\Action;
use Modules\Core\Actions\ActionFields;
use Modules\Core\Fields\User;
use Modules\Core\Http\Requests\ActionRequest;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Users\Models\User as UserModel;
use Modules\Users\Services\UserService;

class UserDelete extends Action
{
    /**
     * Indicates whether this action is destroyable.
     */
    protected bool $destroyable = true;

    /**
     * Handle method.
     */
    public function handle(Collection $models, ActionFields $fields): void
    {
        // User delete action flag
        $service = new UserService;

        // Make sure to position the ID of the current user as the first one in the list.
        // This way, if there's an issue, the "delete" method in the service will fail early.
        $currentUser = $models->first(fn (UserModel $user) => $user->is(Auth::user()));

        if ($currentUser) {
            $models = $models->reject(fn (UserModel $user) => $user->is(Auth::user()))->prepend($currentUser);
        }

        foreach ($models as $model) {
            $service->delete($model, (int) $fields->user_id);
        }
    }

    /**
     * Query the models for execution.
     */
    protected function findModelsForExecution(array $ids, Builder $query): EloquentCollection
    {
        return $query->with(
            ['personalEmailAccounts', 'oAuthAccounts', 'connectedCalendars', 'comments', 'imports']
        )->findMany($ids);
    }

    /**
     * Get the action fields
     */
    public function fields(ResourceRequest $request): array
    {
        return [
            User::make('')
                ->help(__('users::user.transfer_data_info'))
                ->helpDisplay('text')
                ->rules('required'),
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        return $request->user()->isSuperAdmin();
    }

    /**
     * Action name
     */
    public function name(): string
    {
        return __('users::user.actions.delete');
    }
}
