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

namespace Modules\Calls\VoIP\Clients;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Calls\VoIP\Call;
use Modules\Calls\VoIP\Contracts\ReceivesEvents;
use Modules\Calls\VoIP\Contracts\Tokenable;
use Modules\Calls\VoIP\Contracts\VoIPClient;
use Modules\Calls\VoIP\Events\IncomingCallMissed;
use Modules\Calls\VoIP\VoIP;
use Modules\Users\Models\User;
use Twilio\Jwt\ClientToken;
use Twilio\Security\RequestValidator;
use Twilio\TwiML\VoiceResponse;

class Twilio implements ReceivesEvents, Tokenable, VoIPClient
{
    /**
     * Holds the events URL
     *
     * @var string
     */
    protected $eventsUrl;

    /**
     * @var \Twilio\Jwt\ClientToken
     */
    protected $clientToken;

    /**
     * Initialize new Twilio instance.
     */
    public function __construct(protected array $config)
    {
        $this->clientToken = new ClientToken($config['accountSid'], $config['authToken']);
    }

    /**
     * Handle the VoIP service events request
     */
    public function events(Request $request): VoiceResponse
    {
        $action = $request->input('action');

        switch ($action) {
            case 'hold_music':
                return $this->playHoldMusic();
            case 'waiting_message':
                return $this->playWaitingMessage();
            case 'check_agents':
                return $this->checkAgentsAndRedirect($request);
            default:
                return $this->createResponse();
        }
    }

    /**
     * Get the Call class from the given webhook request
     */
    public function getCall(Request $request): Call
    {
        return new Call(
            $this->number(),
            $request->input('From'),
            $request->input('To'),
            $request->input('DialCallStatus')
        );
    }

    /**
     * Create new outgoing call from request
     *
     * @param  string  $phoneNumber
     */
    public function newOutgoingCall($phoneNumber, Request $request): VoiceResponse
    {
        $response = $this->createResponse();

        $response->dial()
            ->setCallerId($this->number())
            ->setAction($this->eventsUrl)
            ->number($phoneNumber);

        return $response;
    }

    /**
     * Create new incoming call from request with IVR support
     */
    public function newIncomingCall(Request $request): VoiceResponse
    {
        $response = $this->createResponse();

        // Check if IVR is enabled
        if (! $this->isIVREnabled()) {
            return $this->handleSimpleIncomingCall($response);
        }

        // Check business hours first
        if ($this->isBusinessHoursEnabled() && ! $this->isBusinessHours()) {
            return $this->handleOutOfHours($response, $request);
        }

        // Play greeting message
        $this->playGreetingMessage($response);

        // Check for available agents
        $availableUsers = $this->getLastActiveUsers();

        if ($availableUsers->isNotEmpty()) {
            // Try to connect to agents
            $dial = $response->dial();

            $availableUsers->each(function ($user) use ($dial) {
                $dial->client($user->getKey());
            });

            $dial->setAction($this->eventsUrl.'?action=check_agents');
        } else {
            // No agents available, start hold pattern immediately
            return $this->startHoldPattern($response);
        }

        return $response;
    }

    /**
     * Handle simple incoming call without IVR
     */
    protected function handleSimpleIncomingCall(VoiceResponse $response): VoiceResponse
    {
        $dial = $response->dial();

        $this->getLastActiveUsers()->each(function ($user) use ($dial) {
            $dial->client($user->getKey());
        });

        $dial->setAction($this->eventsUrl);

        return $response;
    }

    /**
     * Handle calls outside business hours
     */
    protected function handleOutOfHours(VoiceResponse $response, Request $request): VoiceResponse
    {
        $message = $this->getOutOfHoursMessage();

        $response->say($message, [
            'voice' => $this->getVoiceType(),
            'language' => $this->getVoiceLanguage(),
        ]);

        $response->hangup();

        event(new IncomingCallMissed($this->getCall($request)));

        return $response;
    }

    /**
     * Play greeting message
     */
    protected function playGreetingMessage(VoiceResponse $response): void
    {
        $message = $this->getGreetingMessage();

        $response->say($message, [
            'voice' => $this->getVoiceType(),
            'language' => $this->getVoiceLanguage(),
        ]);
    }

    /**
     * Start simple hold pattern
     */
    protected function startHoldPattern(VoiceResponse $response): VoiceResponse
    {
        // Redirect to hold music
        $response->redirect($this->eventsUrl.'?action=hold_music');

        return $response;
    }

    /**
     * Play hold music
     */
    protected function playHoldMusic(): VoiceResponse
    {
        $response = $this->createResponse();

        $holdMusicUrl = $this->getHoldMusicUrl();
        $duration = $this->getWaitingLoopDuration();

        if ($holdMusicUrl) {
            // Play music, but limit the time
            // Note: Twilio will play the entire file unless we use a shorter file
            // or implement a time-based solution
            $response->play($holdMusicUrl);

            // The redirect will happen after the audio file finishes playing
            // So we need short audio files (30-60 seconds max) for this to work properly
        } else {
            // Fallback to silence with controlled duration
            $response->pause(['length' => min($duration, 30)]);
        }

        // This redirect happens AFTER the play/pause completes
        $response->redirect($this->eventsUrl.'?action=waiting_message');

        return $response;
    }

    /**
     * Play waiting message and continue loop
     */
    protected function playWaitingMessage(): VoiceResponse
    {
        $response = $this->createResponse();

        // Play waiting message
        $message = $this->getWaitingMessage();
        $response->say($message, [
            'voice' => $this->getVoiceType(),
            'language' => $this->getVoiceLanguage(),
        ]);

        // Check for agents again
        $response->redirect($this->eventsUrl.'?action=check_agents');

        return $response;
    }

    /**
     * Check for available agents and redirect accordingly
     */
    protected function checkAgentsAndRedirect(?Request $request = null): VoiceResponse
    {
        $response = $this->createResponse();

        // Check if this is a callback from a dial attempt
        if ($request && $request->has('DialCallStatus')) {
            $dialStatus = $request->input('DialCallStatus');

            // Handle different dial statuses
            switch ($dialStatus) {
                case 'completed':
                    // Call was answered successfully, nothing more to do
                    return $response;

                case 'busy':
                case 'no-answer':
                case 'failed':
                case 'canceled':
                    // Call was declined/failed, continue to hold pattern
                    $response->say('Our agents are currently busy. Please continue to hold.', [
                        'voice' => $this->getVoiceType(),
                        'language' => $this->getVoiceLanguage(),
                    ]);

                    return $this->startHoldPattern($response);
            }
        }

        // Regular agent availability check
        $availableUsers = $this->getLastActiveUsers();

        if ($availableUsers->isNotEmpty()) {
            // Agents are available, try to connect
            $response->say('Connecting you to an agent now.', [
                'voice' => $this->getVoiceType(),
                'language' => $this->getVoiceLanguage(),
            ]);

            $dial = $response->dial();

            $availableUsers->each(function ($user) use ($dial) {
                $dial->client($user->getKey());
            });

            $dial->setAction($this->eventsUrl.'?action=check_agents');
        } else {
            // Still no agents, continue hold pattern
            $this->startHoldPattern($response);
        }

        return $response;
    }

    /**
     * Set the events Url
     */
    public function setEventsUrl(string $url): static
    {
        $this->eventsUrl = $url;

        return $this;
    }

    /**
     * Create new client token for the logged in user
     */
    public function newToken(Request $request): string
    {
        $this->clientToken->allowClientOutgoing($this->config['applicationSid']);

        // Set allowed incoming client, @see method newIncomingCall
        $this->clientToken->allowClientIncoming($request->user()->getKey());

        return $this->clientToken->generateToken($request->input('ttl', 3600));
    }

    /**
     * Validate the request for authenticity
     *
     * @return void
     *
     * @see  https://www.twilio.com/docs/usage/security#http-authentication
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function validateRequest(Request $request)
    {
        if (! $signature = $request->server('HTTP_X_TWILIO_SIGNATURE')) {
            abort(403, 'The action you have requested is not allowed [Invalid signature].');
        }

        if ($ngrokUrl = VoIP::getNgrokUrl()) {
            $url = $ngrokUrl.$request->server('REQUEST_URI');
        } elseif ($exposeUrl = VoIP::getExposeUrl()) {
            $url = $exposeUrl.$request->server('REQUEST_URI');
        } else {
            $url = $request->fullUrl();
        }

        $validator = new RequestValidator($this->config['authToken']);

        if (! $validator->validate($signature, $url, $_POST)) {
            abort(404);
        }
    }

    /**
     * Get the Twilio phone number
     *
     * @return string
     */
    public function number()
    {
        return $this->config['number'];
    }

    /**
     *  Get the users that were last active in the last 4 hours
     *  to be available as allowed users to receive calls for the client sdk
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getLastActiveUsers()
    {
        return User::where('last_active_at', '>=', now()->subHours(4))->get();
    }

    /**
     * Create new voice response
     */
    protected function createResponse(): VoiceResponse
    {
        return new VoiceResponse;
    }

    /**
     * Check if IVR is enabled
     */
    protected function isIVREnabled(): bool
    {
        return settings('twilio_ivr_enabled') ?? false;
    }

    /**
     * Check if business hours are enabled
     */
    protected function isBusinessHoursEnabled(): bool
    {
        return settings('twilio_business_hours_enabled') ?? false;
    }

    /**
     * Check if current time is within business hours
     */
    protected function isBusinessHours(): bool
    {
        if (! $this->isBusinessHoursEnabled()) {
            return true;
        }

        $timezone = settings('twilio_timezone') ?: 'UTC';
        $now = Carbon::now($timezone);
        $dayOfWeek = strtolower($now->format('l'));

        $enabled = settings("twilio_{$dayOfWeek}_enabled") ?? false;
        if (! $enabled) {
            return false;
        }

        $start = settings("twilio_{$dayOfWeek}_start") ?: '09:00';
        $end = settings("twilio_{$dayOfWeek}_end") ?: '17:00';

        $startTime = Carbon::createFromTimeString($start, $timezone);
        $endTime = Carbon::createFromTimeString($end, $timezone);

        return $now->between($startTime, $endTime);
    }

    /**
     * Get greeting message
     */
    protected function getGreetingMessage(): string
    {
        return settings('twilio_greeting_message') ?: 'Thank you for calling our company. Please hold while we connect you to an available agent.';
    }

    /**
     * Get waiting message
     */
    protected function getWaitingMessage(): string
    {
        return settings('twilio_waiting_message') ?: 'Thank you for your patience. An agent will be with you shortly.';
    }

    /**
     * Get out of hours message
     */
    protected function getOutOfHoursMessage(): string
    {
        return settings('twilio_out_of_hours_message') ?: 'Thank you for calling. Our office hours are Monday through Friday, 9 AM to 5 PM. Please call back during business hours.';
    }

    /**
     * Get hold music URL
     */
    protected function getHoldMusicUrl(): ?string
    {
        $url = settings('twilio_hold_music_url');

        return $url ?: null;
    }

    /**
     * Get waiting loop duration
     */
    protected function getWaitingLoopDuration(): int
    {
        return settings('twilio_waiting_loop_duration') ?? 30;
    }

    /**
     * Get voice language
     */
    protected function getVoiceLanguage(): string
    {
        return settings('twilio_voice_language') ?: 'en-US';
    }

    /**
     * Get voice type
     */
    protected function getVoiceType(): string
    {
        return settings('twilio_voice_type') ?: 'alice';
    }
}
