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

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Core\Notification;
use Modules\Core\Rules\StringRule;
use Modules\Core\Rules\UniqueRule;
use Modules\Core\Rules\ValidLocaleRule;
use Modules\Users\Models\User;
use Modules\Users\Models\UserInvitation;
use Modules\Users\Services\UserService;

class UserInvitationAcceptController extends Controller
{
    /**
     * Show to invitation accept form.
     */
    public function show(string $token, Request $request): View
    {
        $invitation = UserInvitation::where('token', $token)->firstOrFail();

        $this->abortIfInvitationExpired($invitation);

        return view('users::invitations.show', compact('invitation'));
    }

    /**
     * Accept the invitation and create account.
     */
    public function accept(string $token, Request $request, UserService $service): void
    {
        $invitation = UserInvitation::where('token', $token)->firstOrFail();

        $this->abortIfInvitationExpired($invitation);

        $data = $request->validate([
            'name' => ['required', StringRule::make()],
            'email' => [StringRule::make(), 'email', UniqueRule::make(User::class, null, 'email')],
            'password' => 'required|confirmed|min:6',
            'timezone' => 'required|timezone:all',
            'locale' => ['nullable', new ValidLocaleRule],
            'date_format' => ['required', Rule::in(config('core.date_formats'))],
            'time_format' => ['required', Rule::in(config('core.time_formats'))],
        ]);

        $user = $service->create(new User, array_merge($data, [
            'super_admin' => $invitation->super_admin,
            'access_api' => $invitation->access_api,
            'roles' => $invitation->roles,
            'teams' => $invitation->teams,
            'email' => $invitation->email,
            'notifications' => collect(Notification::preferences())->mapWithKeys(function ($setting) {
                return [$setting['key'] => $setting['channels']->mapWithKeys(function ($channel) {
                    return [$channel => true];
                })->all()];
            })->all(),
        ]));

        Auth::loginUsingId($user->id);

        $invitation->delete();
    }

    /**
     * Abort the request if the given inviation has expired.
     */
    protected function abortIfInvitationExpired(UserInvitation $invitation): void
    {
        abort_if(
            $invitation->created_at->diffInDays() > config('users.invitation.expires_after'),
            404
        );
    }
}
