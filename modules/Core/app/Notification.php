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

namespace Modules\Core;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Modules\Core\Contracts\HasNotificationsSettings;

class Notification extends BaseNotification
{
    use Macroable, Queueable;

    /**
     * All of the available channels for the notifications.
     */
    protected static array $channels = ['mail', 'database', 'broadcast'];

    /**
     * Indicates whether the notification is user configurable.
     */
    public static bool $configurable = true;

    /**
     * Get the notification's delivery channels.
     */
    public function via(HasNotificationsSettings $notifiable): array
    {
        $settings = $notifiable->getNotificationsPreferences(static::key());
        $channels = static::channels();

        // All channels are enabled by default
        if (! count($settings)) {
            return $channels;
        }

        // Filter the channels the user specifically turned off
        $except = array_keys(array_filter(
            $settings, fn (bool $notify) => $notify === false
        ));

        return array_values(array_diff($channels, $except));
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        if (Application::$disableNotifications) {
            return false;
        }

        if (! $notifiable instanceof HasNotificationsSettings) {
            return true;
        }

        // When the user has turned off all notifications, only the "broadcast" channel will be available,
        // we don't need to send the notification as the "broadcast" channel won't have a notification to broadcast.
        return ! ($channel === 'broadcast' && count($this->via($notifiable)) === 1);
    }

    /**
     * Provide the notification available delivery channels.
     */
    public static function channels(): array
    {
        return array_unique(static::$channels);
    }

    /**
     * Add additional delivery channel for the notifications.
     */
    public static function addChannel(string $channel): void
    {
        if (! in_array($channel, static::$channels)) {
            static::$channels[] = $channel;
        }
    }

    /**
     * Get the notification unique key identifier.
     */
    public static function key(): string
    {
        return Str::snake(class_basename(get_called_class()), '-');
    }

    /**
     * Get the displayable name of the notification.
     */
    public static function name(): string
    {
        return Str::title(Str::snake(class_basename(get_called_class()), ' '));
    }

    /**
     * Provide the notification description.
     */
    public static function description(): ?string
    {
        return null;
    }

    /**
     * Get all the notifications information for front-end.
     */
    public static function preferences(?HasNotificationsSettings $for = null): array
    {
        $preferences = collect(Application::getRegisteredNotifications())
            ->filter(fn (string $notification) => $notification::$configurable)
            ->map(function ($notification) use ($for) {
                return array_merge([
                    'key' => $notification::key(),
                    'name' => $notification::name(),
                    'description' => $notification::description(),
                    'channels' => $channels = collect($notification::channels())
                        ->reject(
                            fn (string $channel) => $channel === 'broadcast'
                        )->values(),

                ], is_null($for) ? [] : ['availability' => array_merge(
                    $channels->mapWithKeys(fn (string $channel) => [$channel => true])->all(),
                    $for->getNotificationsPreferences($notification::key())
                )]);
            });

        $preferences = apply_filters('notification.preferences', $preferences);

        $preferences = $preferences->values()->all();

        return $preferences;
    }
}
