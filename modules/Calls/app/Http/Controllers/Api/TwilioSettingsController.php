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

namespace Modules\Calls\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Http\Controllers\ApiController;

class TwilioSettingsController extends ApiController
{
    /**
     * Get all Twilio settings including IVR configuration
     */
    public function getSettings(): JsonResponse
    {
        $settings = [
            // Basic Twilio settings
            'twilio_account_sid' => settings('twilio_account_sid') ?: '',
            'twilio_auth_token' => settings('twilio_auth_token') ?: '',
            'twilio_app_sid' => settings('twilio_app_sid') ?: '',
            'twilio_number' => settings('twilio_number') ?: '',

            // IVR Configuration
            'twilio_ivr_enabled' => settings('twilio_ivr_enabled') ?? false,
            'twilio_business_hours_enabled' => settings('twilio_business_hours_enabled') ?? false,
            'twilio_timezone' => settings('twilio_timezone') ?: 'America/New_York',

            // Business Hours
            'twilio_monday_enabled' => settings('twilio_monday_enabled') ?? true,
            'twilio_monday_start' => settings('twilio_monday_start') ?: '09:00',
            'twilio_monday_end' => settings('twilio_monday_end') ?: '17:00',
            'twilio_tuesday_enabled' => settings('twilio_tuesday_enabled') ?? true,
            'twilio_tuesday_start' => settings('twilio_tuesday_start') ?: '09:00',
            'twilio_tuesday_end' => settings('twilio_tuesday_end') ?: '17:00',
            'twilio_wednesday_enabled' => settings('twilio_wednesday_enabled') ?? true,
            'twilio_wednesday_start' => settings('twilio_wednesday_start') ?: '09:00',
            'twilio_wednesday_end' => settings('twilio_wednesday_end') ?: '17:00',
            'twilio_thursday_enabled' => settings('twilio_thursday_enabled') ?? true,
            'twilio_thursday_start' => settings('twilio_thursday_start') ?: '09:00',
            'twilio_thursday_end' => settings('twilio_thursday_end') ?: '17:00',
            'twilio_friday_enabled' => settings('twilio_friday_enabled') ?? true,
            'twilio_friday_start' => settings('twilio_friday_start') ?: '09:00',
            'twilio_friday_end' => settings('twilio_friday_end') ?: '17:00',
            'twilio_saturday_enabled' => settings('twilio_saturday_enabled') ?? false,
            'twilio_saturday_start' => settings('twilio_saturday_start') ?: '09:00',
            'twilio_saturday_end' => settings('twilio_saturday_end') ?: '17:00',
            'twilio_sunday_enabled' => settings('twilio_sunday_enabled') ?? false,
            'twilio_sunday_start' => settings('twilio_sunday_start') ?: '09:00',
            'twilio_sunday_end' => settings('twilio_sunday_end') ?: '17:00',

            // Messages
            'twilio_greeting_message' => settings('twilio_greeting_message') ?: 'Thank you for calling our company. Please hold while we connect you to an available agent.',
            'twilio_waiting_message' => settings('twilio_waiting_message') ?: 'Thank you for your patience. An agent will be with you shortly.',
            'twilio_out_of_hours_message' => settings('twilio_out_of_hours_message') ?: 'Thank you for calling. Our office hours are Monday through Friday, 9 AM to 5 PM. Please call back during business hours.',

            // Audio and Advanced Settings
            'twilio_hold_music_url' => settings('twilio_hold_music_url') ?: '',
            'twilio_waiting_loop_duration' => settings('twilio_waiting_loop_duration') ?? 30,
            'twilio_voice_language' => settings('twilio_voice_language') ?: 'en-US',
            'twilio_voice_type' => settings('twilio_voice_type') ?: 'alice',
        ];

        return $this->response($settings);
    }

    /**
     * Save all Twilio settings including IVR configuration
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $validator = $this->validateSettings($request);

        if ($validator->fails()) {
            return $this->response(['errors' => $validator->errors()], 422);
        }

        $settingsToSave = [
            // Basic Twilio settings
            'twilio_account_sid' => $request->input('twilio_account_sid'),
            'twilio_auth_token' => $request->input('twilio_auth_token'),
            'twilio_app_sid' => $request->input('twilio_app_sid'),
            'twilio_number' => $request->input('twilio_number'),

            // IVR Configuration
            'twilio_ivr_enabled' => $request->boolean('twilio_ivr_enabled'),
            'twilio_business_hours_enabled' => $request->boolean('twilio_business_hours_enabled'),
            'twilio_timezone' => $request->input('twilio_timezone', 'America/New_York'),

            // Business Hours
            'twilio_monday_enabled' => $request->boolean('twilio_monday_enabled'),
            'twilio_monday_start' => $request->input('twilio_monday_start', '09:00'),
            'twilio_monday_end' => $request->input('twilio_monday_end', '17:00'),
            'twilio_tuesday_enabled' => $request->boolean('twilio_tuesday_enabled'),
            'twilio_tuesday_start' => $request->input('twilio_tuesday_start', '09:00'),
            'twilio_tuesday_end' => $request->input('twilio_tuesday_end', '17:00'),
            'twilio_wednesday_enabled' => $request->boolean('twilio_wednesday_enabled'),
            'twilio_wednesday_start' => $request->input('twilio_wednesday_start', '09:00'),
            'twilio_wednesday_end' => $request->input('twilio_wednesday_end', '17:00'),
            'twilio_thursday_enabled' => $request->boolean('twilio_thursday_enabled'),
            'twilio_thursday_start' => $request->input('twilio_thursday_start', '09:00'),
            'twilio_thursday_end' => $request->input('twilio_thursday_end', '17:00'),
            'twilio_friday_enabled' => $request->boolean('twilio_friday_enabled'),
            'twilio_friday_start' => $request->input('twilio_friday_start', '09:00'),
            'twilio_friday_end' => $request->input('twilio_friday_end', '17:00'),
            'twilio_saturday_enabled' => $request->boolean('twilio_saturday_enabled'),
            'twilio_saturday_start' => $request->input('twilio_saturday_start', '09:00'),
            'twilio_saturday_end' => $request->input('twilio_saturday_end', '17:00'),
            'twilio_sunday_enabled' => $request->boolean('twilio_sunday_enabled'),
            'twilio_sunday_start' => $request->input('twilio_sunday_start', '09:00'),
            'twilio_sunday_end' => $request->input('twilio_sunday_end', '17:00'),

            // Messages
            'twilio_greeting_message' => $request->input('twilio_greeting_message'),
            'twilio_waiting_message' => $request->input('twilio_waiting_message'),
            'twilio_out_of_hours_message' => $request->input('twilio_out_of_hours_message'),

            // Audio and Advanced Settings
            'twilio_hold_music_url' => $request->input('twilio_hold_music_url'),
            'twilio_waiting_loop_duration' => $request->input('twilio_waiting_loop_duration', 30),
            'twilio_voice_language' => $request->input('twilio_voice_language', 'en-US'),
            'twilio_voice_type' => $request->input('twilio_voice_type', 'alice'),
        ];

        settings()->set($settingsToSave)->save();

        return $this->response(JsonResponse::HTTP_NO_CONTENT);

    }

    /**
     * Test audio URL accessibility
     */
    public function testAudioUrl(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return $this->response(['valid' => false, 'message' => 'Invalid URL format'], 422);
        }

        $url = $request->input('url');
        $isValid = $this->isValidAudioUrl($url);

        return $this->response([
            'valid' => $isValid,
            'message' => $isValid ? 'Audio URL is accessible' : 'Audio URL is not accessible or not a valid audio file',
            'url' => $url,
        ]);
    }

    /**
     * Validate all settings
     */
    private function validateSettings(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            // Basic Twilio validation
            'twilio_account_sid' => 'nullable|string|max:255',
            'twilio_auth_token' => 'nullable|string|max:255',
            'twilio_app_sid' => 'nullable|string|max:255',
            'twilio_number' => 'nullable|string|max:255',

            // IVR Configuration validation
            'twilio_ivr_enabled' => 'boolean',
            'twilio_business_hours_enabled' => 'boolean',
            'twilio_timezone' => 'nullable|string|in:'.implode(',', timezone_identifiers_list()),

            // Business Hours validation
            'twilio_monday_enabled' => 'boolean',
            'twilio_monday_start' => 'nullable|date_format:H:i',
            'twilio_monday_end' => 'nullable|date_format:H:i|after:twilio_monday_start',
            'twilio_tuesday_enabled' => 'boolean',
            'twilio_tuesday_start' => 'nullable|date_format:H:i',
            'twilio_tuesday_end' => 'nullable|date_format:H:i|after:twilio_tuesday_start',
            'twilio_wednesday_enabled' => 'boolean',
            'twilio_wednesday_start' => 'nullable|date_format:H:i',
            'twilio_wednesday_end' => 'nullable|date_format:H:i|after:twilio_wednesday_start',
            'twilio_thursday_enabled' => 'boolean',
            'twilio_thursday_start' => 'nullable|date_format:H:i',
            'twilio_thursday_end' => 'nullable|date_format:H:i|after:twilio_thursday_start',
            'twilio_friday_enabled' => 'boolean',
            'twilio_friday_start' => 'nullable|date_format:H:i',
            'twilio_friday_end' => 'nullable|date_format:H:i|after:twilio_friday_start',
            'twilio_saturday_enabled' => 'boolean',
            'twilio_saturday_start' => 'nullable|date_format:H:i',
            'twilio_saturday_end' => 'nullable|date_format:H:i|after:twilio_saturday_start',
            'twilio_sunday_enabled' => 'boolean',
            'twilio_sunday_start' => 'nullable|date_format:H:i',
            'twilio_sunday_end' => 'nullable|date_format:H:i|after:twilio_sunday_start',

            // Message validation
            'twilio_greeting_message' => 'nullable|string|max:500',
            'twilio_waiting_message' => 'nullable|string|max:500',
            'twilio_out_of_hours_message' => 'nullable|string|max:1000',

            // Audio and Advanced Settings validation
            'twilio_hold_music_url' => 'nullable|url|max:500',
            'twilio_waiting_loop_duration' => 'integer|min:10|max:120',
            'twilio_voice_language' => 'nullable|string|in:en-US,en-GB,es-ES,es-MX,fr-FR,de-DE,it-IT,pt-BR',
            'twilio_voice_type' => 'nullable|string|in:alice,man,woman',
        ], [
            // Custom error messages
            'twilio_monday_end.after' => 'Monday end time must be after start time',
            'twilio_tuesday_end.after' => 'Tuesday end time must be after start time',
            'twilio_wednesday_end.after' => 'Wednesday end time must be after start time',
            'twilio_thursday_end.after' => 'Thursday end time must be after start time',
            'twilio_friday_end.after' => 'Friday end time must be after start time',
            'twilio_saturday_end.after' => 'Saturday end time must be after start time',
            'twilio_sunday_end.after' => 'Sunday end time must be after start time',
            'twilio_greeting_message.max' => 'Greeting message must not exceed 500 characters',
            'twilio_waiting_message.max' => 'Waiting message must not exceed 500 characters',
            'twilio_out_of_hours_message.max' => 'Out of hours message must not exceed 1000 characters',
            'twilio_waiting_loop_duration.min' => 'Waiting loop duration must be at least 10 seconds',
            'twilio_waiting_loop_duration.max' => 'Waiting loop duration must not exceed 120 seconds',
            'twilio_hold_music_url.url' => 'Hold music URL must be a valid URL',
            'twilio_hold_music_url.max' => 'Hold music URL must not exceed 500 characters',
        ]);
    }

    /**
     * Check if audio URL is valid and accessible
     */
    private function isValidAudioUrl(string $url): bool
    {
        try {
            $response = Http::timeout(10)
                ->withUserAgent('Concord CRM Audio Checker')
                ->withoutVerifying()
                ->head($url);

            // Check if URL is accessible (HTTP 200)
            if (! $response->successful()) {
                return false;
            }

            // Check if content type indicates audio file
            $contentType = $response->header('Content-Type');
            if ($contentType && strpos(strtolower($contentType), 'audio') !== false) {
                return true;
            }

            // If content type is not clearly audio, check file extension
            $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
            $extension = strtolower($pathInfo['extension'] ?? '');

            return in_array($extension, ['mp3', 'wav', 'mp4', 'ogg', 'm4a']);

        } catch (Exception) {
            return false;
        }
    }
}
