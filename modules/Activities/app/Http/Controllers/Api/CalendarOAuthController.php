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

namespace Modules\Activities\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Modules\Activities\Http\Resources\CalendarResource;
use Modules\Activities\Models\Calendar;
use Modules\Core\Common\Calendar\CalendarManager;
use Modules\Core\Common\Calendar\Exceptions\UnauthorizedException;
use Modules\Core\Common\OAuth\EmptyRefreshTokenException;
use Modules\Core\Common\Synchronization\Exceptions\InvalidSyncNotificationURLException;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Models\OAuthAccount;

class CalendarOAuthController extends ApiController
{
    /**
     * Error messages.
     */
    protected array $messages = [
        'invalid_url' => 'We were unable to verify the notification URL for changes, make sure that your installation is publicly accessible, your installation URL starts with "https" and using valid SSL certificate.',

        'oauth_requires_auth' => 'Synchronization disabled because the OAuth account requires authentication',
    ];

    /**
     * Get the user connected OAuth calendar.
     */
    public function index(Request $request): ?JsonResponse
    {
        $calendar = Calendar::findActiveFor($request->user());

        return $calendar ? $this->response(
            new CalendarResource($calendar->load('oAuthAccount'))
        ) : null;
    }

    /**
     * Get the available calendars for the given OAuth Account.
     */
    public function calendars(string $id): JsonResponse
    {
        $account = $this->getAccount($id);

        $this->authorize('view', $account);

        try {
            return $this->response($this->getCalendars($account));
        } catch (EmptyRefreshTokenException) {
            abort(500, 'The calendars cannot be retrieved from the '.$account->email.' account because the account has empty refresh token, try to remove the app from your '.explode('@', $account->email)[1].' account connected apps section and re-connect again.');
        } catch (IdentityProviderException) {
            $account->setAuthRequired();
        } catch (UnauthorizedException $e) {
            abort(500, $e->getMessage());
        }
    }

    /**
     * Connect the user connected OAuth account.
     */
    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'activity_type_id' => 'required|numeric',
            'access_token_id' => 'required|numeric',
            'activity_types' => 'required|array',
            'calendar_id' => ['required', 'string', Rule::in(
                $this->getCalendars($this->getAccount($request->access_token_id))->map(fn ($calendar) => $calendar->getId())
            )],
        ]);

        /** @var \Modules\Activities\Models\Calendar */
        $calendar = Calendar::where('calendar_id', $data['calendar_id'])
            ->where('user_id', $request->user()->getKey())
            ->first() ?: new Calendar;

        try {
            $calendar->forceFill([
                'calendar_id' => $data['calendar_id'],
                'activity_type_id' => $data['activity_type_id'],
                'activity_types' => $data['activity_types'],
                'access_token_id' => $data['access_token_id'],
                'email' => OAuthAccount::find($data['access_token_id'])->email,
                'user_id' => $request->user()->getKey(),
            ])->save();

            $calendar->loadMissing('synchronization.synchronizable');

            $calendar->synchronization->enableSync();
        } catch (InvalidSyncNotificationURLException) {
            $calendar->synchronization->stopSync($this->messages['invalid_url']);
        }

        // if ($originalCalendar && $data['calendar_id'] != $originalCalendar->calendar_id) {
        //     $this->disableSync($originalCalendar);
        // }

        $calendar = $calendar->fresh()->load('oAuthAccount');

        return $this->response(new CalendarResource($calendar));
    }

    /**
     * Stop the sync for the connected calendar account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $calendar = Calendar::findActiveFor($request->user());

        abort_unless($calendar, 404);

        try {
            $calendar->disableSync();
        } catch (EmptyRefreshTokenException|IdentityProviderException|UnauthorizedException) {
        }

        return $this->response(new CalendarResource(
            $calendar->loadMissing('oAuthAccount')
        ));
    }

    protected function getAccount($id)
    {
        return OAuthAccount::find($id);
    }

    protected function determineConnectionType($account)
    {
        return match ($account->type) {
            'microsoft' => 'outlook',
            default => $account->type,
        };
    }

    protected function getCalendars($account)
    {
        return collect(CalendarManager::createClient(
            $this->determineConnectionType($account),
            $account->tokenProvider()
        )->getCalendars())
            ->filter->isDefault()
            ->values();
    }
}
