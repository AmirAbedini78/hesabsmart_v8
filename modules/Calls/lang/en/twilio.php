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

return [
    'twilio' => 'Twilio',
    'create_app' => 'Create Application',
    'disconnect' => 'Disconnect Integration',
    'number' => 'Select Twilio number from your account that will be used to make and receive calls.',
    'retrieve_numbers' => 'Retrieve Numbers',
    'app' => 'Create application that will handle initiating new calls and incoming calls.',
    'app_url_warning' => 'Your Twilio application URL does match your installation URL.',
    'update_url' => 'Update URL',
    'https_alert' => 'Application must be served over HTTPS URL in order to use the Twilio integration.',
    'account_sid' => 'Account SID',
    'auth_token' => 'Auth Token',
    'no_voice_capabilities' => 'This phone number does not have enabled voice capabilities.',

    // New IVR translations
    'ivr_settings' => 'IVR Settings',
    'enable_ivr' => 'Enable IVR',
    'business_hours' => 'Business Hours',
    'enable_business_hours' => 'Enable Business Hours',
    'timezone' => 'Timezone',
    'ivr_messages' => 'IVR Messages',
    'greeting_message' => 'Greeting Message',
    'greeting_placeholder' => 'Thank you for calling our company. Please hold while we connect you to an available agent.',
    'waiting_message' => 'Waiting Message',
    'waiting_placeholder' => 'Thank you for your patience. An agent will be with you shortly.',
    'out_of_hours_message' => 'Out of Hours Message',
    'out_of_hours_placeholder' => 'Thank you for calling. Our office hours are Monday through Friday, 9 AM to 5 PM. Please call back during business hours.',
    'message_tip' => 'Keep messages concise and professional. They will be converted to speech.',

    'hold_music' => 'Hold Music',
    'hold_music_url' => 'Hold Music URL',
    'hold_music_placeholder' => 'https://your-domain.com/audio/hold-music.mp3',
    'test_audio' => 'Test Audio',
    'additional_audio_sources' => 'Where to Find Audio Files',
    'free_sources' => 'Free Audio Sources',
    'royalty_free_sounds' => 'Royalty-free sounds and music',
    'professional_audio' => 'Professional audio effects',
    'background_music' => 'Royalty-free background music',
    'tip' => 'Tip',
    'url_tip' => 'Host your audio files on your server or use a CDN. Make sure the URL is publicly accessible and returns the audio file directly. Adjust length of the audio to fit your needs, the visitor will wait until the audio finishes playing.',
    'audio_requirements' => 'Supported formats: MP3, WAV. Direct file URLs only. File should be publicly accessible.',
    'audio_test_failed' => 'Could not play audio file. Please check the URL and ensure it\'s publicly accessible.',
    'invalid_url_format' => 'Please enter a valid URL format.',
    'audio_test_success' => 'Audio URL is working correctly!',
    'url_not_accessible' => 'The URL is not accessible. Please check if the file exists and is publicly available.',
    'audio_aborted' => 'Audio loading was aborted.',
    'audio_network_error' => 'Network error while loading audio.',
    'audio_decode_error' => 'Audio file format is not supported or corrupted.',
    'audio_unknown_error' => 'Unknown error occurred while testing audio.',
    'audio_play_failed' => 'Could not play audio file.',
    'cors_error' => 'Cannot test audio from {domain} due to CORS restrictions.',
    'cors_solution' => 'The URL will still work for Twilio calls, but browser testing is blocked.',
    'cors_note' => 'Testing Limitation',
    'cors_explanation' => 'Some external URLs cannot be tested in the browser due to CORS policies, but they will still work perfectly for Twilio calls.',

    'advanced_settings' => 'Advanced Settings',
    'waiting_loop_duration' => 'Waiting Loop Duration',
    'seconds' => 'seconds',
    'loop_duration_tip' => 'Time between waiting messages (10-120 seconds).',
    'voice_language' => 'Voice Language',
    'voice_type' => 'Voice Type',

    'call_flow_preview' => 'Call Flow Preview',
    'step_1' => 'Incoming call received',
    'step_2' => 'Greeting message played',
    'step_3' => 'Hold music and waiting messages',
    'step_4' => 'Connect to available agent',
];
