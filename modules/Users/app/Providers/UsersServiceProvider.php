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

namespace Modules\Users\Providers;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Core\SystemInfo;
use Modules\Users\Http\Resources\UserResource;
use Modules\Users\Models\User;

class UsersServiceProvider extends ModuleServiceProvider
{
    protected bool $withViews = true;

    protected array $resources = [
        \Modules\Users\Resources\User::class,
    ];

    protected array $mailableTemplates = [
        \Modules\Users\Mail\ResetPassword::class,
        \Modules\Users\Mail\InvitationCreated::class,
        \Modules\Users\Mail\UserMentioned::class,
    ];

    protected array $notifications = [
        \Modules\Users\Notifications\ResetPassword::class,
        \Modules\Users\Notifications\UserMentioned::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        $this->booting(function () {
            Gate::before(function ($user, $ability) {
                $result = $user->isSuperAdmin() ? true : null;

                return apply_filters_ref_array('gate.before', [$result, $user, $ability]);
            });
            Gate::define('is-super-admin', fn ($user) => $user->isSuperAdmin());
            Gate::define('access-api', fn ($user) => $user->hasApiAccess());
        });
    }

    /**
     * Configure the module.
     */
    protected function setup(): void
    {
        SystemInfo::register(
            'USER_INVITATION_EXPIRES_AFTER',
            $this->app['config']->get('users.invitation.expires_after')
        );
    }

    /**
     * Provide the data to share on the front-end.
     */
    protected function scriptData(): Closure
    {
        return fn () => Auth::check() ? [
            'user_id' => Auth::id(),
            'users' => UserResource::collection(
                User::withCommon()->get()
            ),
            'invitation' => [
                'expires_after' => config('users.invitation.expires_after'),
            ],
        ] : [];
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Users';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'users';
    }
}
