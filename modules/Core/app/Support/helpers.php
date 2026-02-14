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

use Akaunting\Money\Currency;
use Illuminate\Support\Str;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Hooks;
use Modules\Core\Settings\Contracts\Manager as SettingsManager;

if (! function_exists('format_bytes')) {
    /**
     * Format the given bytes in a proper human readable format.
     *
     * @param  int|float  $bytes
     */
    function format_bytes($bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}

if (! function_exists('timezone')) {
    /**
     * Helper timezone function.
     *
     * @return \Modules\Core\Timezone
     */
    function timezone()
    {
        return \Modules\Core\Facades\Timezone::getFacadeRoot();
    }
}

if (! function_exists('tz')) {
    /**
     * Alias to timezone() function.
     *
     * @return \Modules\Core\Timezone
     */
    function tz()
    {
        return timezone();
    }
}

if (! function_exists('get_generated_lang')) {
    /**
     * Get the application generate language.
     */
    function get_generated_lang(?string $locale = null): object
    {
        $path = config('translator.json');

        if (! is_file($path)) {
            return (object) [];
        }

        $content = json_decode(
            file_get_contents($path)
        );

        if (is_null($locale)) {
            return $content;
        }

        return tap(new stdClass, function ($localeClass) use ($content, $locale) {
            if (isset($content->{$locale})) {
                $localeClass->{$locale} = $content->{$locale};
            } else {
                foreach ([config('app.locale'), config('app.fallback_locale')] as $fallback) {
                    if (isset($content->{$fallback})) {
                        $localeClass->{$fallback} = $content->{$fallback};

                        break;
                    }
                }
            }
        });
    }
}

if (! function_exists('is_rtl')) {
    /**
     * Determine if the current locale is right-to-left (e.g. Persian, Arabic).
     *
     * @param  string|null  $locale
     * @return bool
     */
    function is_rtl(?string $locale = null): bool
    {
        $locale = $locale ?? app()->getLocale();

        return in_array($locale, ['fa', 'fa_IR', 'ar', 'ar_AE', 'ar_SA'], true);
    }
}

if (! function_exists('clone_prefix')) {
    /**
     * Add clone prefix to the given string.
     *
     * Can be used when cloning models, to generaet unique name/title etc...
     */
    function clone_prefix(string $to): string
    {
        $title = preg_replace('/\s-\sCopy\([a-zA-Z0-9]{6}+\)/', '', $to);

        return $title.' - Copy('.Str::random(6).')';
    }
}

if (! function_exists('privacy_url')) {
    /**
     * Application privacy policy url.
     */
    function privacy_url(): string
    {
        return url('/privacy-policy');
    }
}

if (! function_exists('settings')) {
    /**
     * Get the settings manager instance.
     *
     * @param  string|array|null  $driver
     * @param  bool  $save
     * @return mixed
     */
    function settings($driver = null, $save = true)
    {
        $manager = app(SettingsManager::class);

        if ($driver) {
            if (is_array($driver)) {
                return tap($manager->set($driver), fn ($instance) => $save && $instance->save());
            }

            if (in_array($driver, array_keys(config('settings.drivers')))) {
                return $manager->driver($driver);
            }

            return $manager->get($driver);
        }

        return $manager;
    }
}

if (! function_exists('clean')) {
    /**
     * Clean the given string.
     *
     * @param  string  $dirty
     * @param  mixed  $config
     * @return string
     */
    function clean($dirty, $config = null)
    {
        return app('purifier')->clean($dirty, $config);
    }
}

if (! function_exists('get_current_process_user')) {
    /**
     * Get the current PHP process user.
     *
     * The function returns the process user not the file owner user like get_current_user().
     */
    function get_current_process_user(): string
    {
        if (! function_exists('posix_getpwuid')) {
            return get_current_user();
        }

        return posix_getpwuid(posix_geteuid())['name'] ?? null;
    }
}

if (! function_exists('forgot_password_is_disabled')) {
    /**
     * Check if the forgot password auth feature is disabled.
     */
    function forgot_password_is_disabled(): bool
    {
        return settings('disable_password_forgot') === true;
    }
}

if (! function_exists('to_money')) {
    /**
     * Create new Money instance.
     *
     * @param  string|int|float  $value
     * @return \Akaunting\Money\Money
     */
    function to_money($value, string|Currency|null $currency = null)
    {
        return Innoclapps::currency($currency)->toMoney($value);
    }
}

if (! function_exists('set_alert')) {
    /**
     * Set web alert.
     *
     * @param  string|null  $message
     * @param  string  $variant
     * @return void
     */
    function set_alert($message, $variant)
    {
        session()->flash($variant, $message);
    }
}

if (! function_exists('get_current_alert')) {
    /**
     * Get the alert in session.
     */
    function get_current_alert(): ?array
    {
        foreach (['primary', 'success', 'info', 'warning', 'danger'] as $type) {
            if ($message = session()->get($type)) {
                return ['variant' => $type, 'message' => $message];
            }
        }

        return null;
    }
}

if (! function_exists('html_to_text')) {
    /**
     * Convert HTML to Text
     *
     * @param  string  $html
     * @return string
     */
    function html_to_text($html)
    {
        return \Soundasleep\Html2Text::convert($html, ['ignore_errors' => true]);
    }
}

if (! function_exists('hooks')) {
    /**
     * Get the hook instance.
     */
    function hooks(): Hooks
    {
        return app('hooks');
    }
}

if (! function_exists('do_action')) {
    /**
     * Execute functions hooked on a specific action hook.
     */
    function do_action(string $tag, mixed $arg = null, ...$args): ?true
    {
        return hooks()->doAction($tag, $arg, ...$args);
    }
}

if (! function_exists('do_action_ref_array')) {
    /**
     * Execute functions hooked on a specific action hook, specifying arguments in an array.
     */
    function do_action_ref_array(string $tag, array $args, ...$extra): ?true
    {
        return hooks()->doActionRefArray($tag, $args, ...$extra);
    }
}

if (! function_exists('add_action')) {
    /**
     * Hooks a function on to a specific action.
     */
    function add_action(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        return hooks()->addAction($tag, $callback, $priority, $acceptedArgs);
    }
}

if (! function_exists('has_action')) {
    /**
     * Check if any action has been registered for a hook.
     */
    function has_action(string $tag, callable|false $callback = false): bool|int
    {
        return hooks()->hasAction($tag, $callback);
    }
}

if (! function_exists('remove_action')) {
    /**
     * Removes a function from a specified action hook.
     */
    function remove_action(string $tag, callable $callback, int $priority = 10): bool
    {
        return hooks()->removeAction($tag, $callback, $priority);
    }
}

if (! function_exists('did_action')) {
    /**
     * Get the number of times an action is fired.
     */
    function did_action(string $tag): int
    {
        return hooks()->didAction($tag);
    }
}

if (! function_exists('doing_action')) {
    /**
     * Check whether currently an action is being executed.
     */
    function doing_action(?string $action = null): bool
    {
        return hooks()->doingAction($action);
    }
}

if (! function_exists('current_action')) {
    /**
     * Retrieve the name of the current action.
     */
    function current_action(): ?string
    {
        return hooks()->currentAction();
    }
}

if (! function_exists('remove_all_actions')) {
    /**
     * Remove all of the hooks from an action.
     */
    function remove_all_actions(string $tag, false|int $priority = false): bool
    {
        return hooks()->removeAllActions($tag, $priority);
    }
}

if (! function_exists('apply_filters')) {
    /**
     * Call the functions added to a filter hook.
     */
    function apply_filters(string $tag, mixed $value, ...$args): mixed
    {
        return hooks()->applyFilters($tag, $value, ...$args);
    }
}

if (! function_exists('apply_filters_ref_array')) {
    /**
     * Execute functions hooked on a specific filter hook, specifying arguments in an array.
     */
    function apply_filters_ref_array(string $tag, array $args, ...$extra): mixed
    {
        return hooks()->applyFiltersRefArray($tag, $args, ...$extra);
    }
}

if (! function_exists('add_filter')) {
    /**
     * Hooks a function or method to a specific filter action.
     */
    function add_filter(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
    {
        return hooks()->addFilter($tag, $callback, $priority, $acceptedArgs);
    }
}

if (! function_exists('has_filter')) {
    /**
     * Check if any filter has been registered for a hook.
     */
    function has_filter(string $tag, callable|false $callback = false): bool|int
    {
        return hooks()->hasFilter($tag, $callback);
    }
}

if (! function_exists('remove_filter')) {
    /**
     * Removes a function from a specified filter hook.
     */
    function remove_filter(string $tag, callable $callback, int $priority = 10): bool
    {
        return hooks()->removeFilter($tag, $callback, $priority);
    }
}

if (! function_exists('doing_filter')) {
    /**
     * Check whether currently a filter is being executed.
     */
    function doing_filter(?string $filter = null): bool
    {
        return hooks()->doingFilter($filter);
    }
}

if (! function_exists('current_filter')) {
    /**
     * Retrieve the name of the current filter or action.
     */
    function current_filter(): ?string
    {
        return hooks()->currentFilter();
    }
}

if (! function_exists('remove_all_filters')) {
    /**
     * Remove all of the hooks from a filter.
     */
    function remove_all_filters(string $tag, int|false $priority = false): true
    {
        return hooks()->removeAllFilters($tag, $priority);
    }
}
