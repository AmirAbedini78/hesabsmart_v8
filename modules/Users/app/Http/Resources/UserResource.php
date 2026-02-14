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

namespace Modules\Users\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Http\Resources\DashboardResource;
use Modules\Core\Http\Resources\RoleResource;
use Modules\Core\Notification;
use Modules\Core\Resource\JsonResource;

/** @mixin \Modules\Users\Models\User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name' => $this->name,
            'email' => $this->email,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'avatar' => $this->avatar,
            'uploaded_avatar_url' => $this->uploaded_avatar_url,
            'avatar_url' => $this->avatar_url,
            'mail_signature' => clean($this->mail_signature),
            $this->mergeWhen(! $request->isZapier(), [
                'guest_email' => $this->getGuestEmail(),
                'guest_display_name' => $this->getGuestDisplayName(),
                'teams' => TeamResource::collection($this->whenLoaded('teams', fn () => $this->allTeams(), [])),
                'super_admin' => $this->super_admin,
                'access_api' => $this->access_api,
                'time_format' => $this->time_format,
                'date_format' => $this->date_format,
                'default_landing_page' => $this->default_landing_page,
                $this->mergeWhen($this->isCurrent() && $this->relationLoaded('dashboards'), [
                    'dashboards' => DashboardResource::collection($this->whenLoaded('dashboards')),
                ]),
                'notifications' => [
                    $this->mergeWhen($this->isCurrent() && ! is_null($this->unread_notifications_count), [
                        'unread_count' => (int) $this->unread_notifications_count,
                    ]),
                    // Admin user edit and profile
                    $this->mergeWhen($this->adminLoggedIn() || $this->isCurrent(), [
                        'settings' => Notification::preferences($this->resource),
                    ]),
                ],
                $this->mergeWhen($this->isCurrent(), function () {
                    return [
                        'permissions' => $this->getPermissionsViaRoles()->pluck('name'),
                    ];
                }),
                $this->mergeWhen($this->whenLoaded('roles') && $this->adminLoggedIn(), [
                    'roles' => RoleResource::collection($this->whenLoaded('roles')),
                ]),
            ]),
        ], $request);
    }

    /**
     * Determine if the user for the resource is the current logged-in user.
     */
    protected function isCurrent(): bool
    {
        return $this->is(Auth::user());
    }

    /**
     * Determine if the logged-in user is super admin.
     */
    protected function adminLoggedIn(): bool
    {
        /** @var \Modules\Users\Models\User */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin();
    }
}
