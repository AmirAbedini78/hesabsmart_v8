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

namespace Modules\Activities\Providers;

use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Auth;
use Modules\Activities\Console\Commands\SendOverdueNotifications;
use Modules\Activities\Console\Commands\SyncNextActivityDate;
use Modules\Activities\Http\Resources\ActivityTypeResource;
use Modules\Activities\Listeners\StopRelatedOAuthCalendars;
use Modules\Activities\Listeners\TransferActivitiesUserData;
use Modules\Activities\Menu\TodaysActivitiesMetric;
use Modules\Activities\Models\ActivityType;
use Modules\Core\Common\OAuth\Events\OAuthAccountDeleting;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\Menu;
use Modules\Core\Pages\Tab;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Core\SystemInfo;
use Modules\Users\Events\TransferringUserData;

class ActivitiesServiceProvider extends ModuleServiceProvider
{
    protected array $resources = [
        \Modules\Activities\Resources\Activity::class,
        \Modules\Activities\Resources\ActivityType::class,
    ];

    protected array $mailableTemplates = [
        \Modules\Activities\Mail\ActivityReminder::class,
        \Modules\Activities\Mail\ContactAttendsToActivity::class,
        \Modules\Activities\Mail\UserAssignedToActivity::class,
        \Modules\Activities\Mail\UserAttendsToActivity::class,
    ];

    protected array $notifications = [
        \Modules\Activities\Notifications\ActivityReminder::class,
        \Modules\Activities\Notifications\UserAssignedToActivity::class,
        \Modules\Activities\Notifications\UserAttendsToActivity::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->registerCommands();

        $this->app['events']->listen(OAuthAccountDeleting::class, StopRelatedOAuthCalendars::class);
        $this->app['events']->listen(TransferringUserData::class, TransferActivitiesUserData::class);
    }

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Configure the module.
     */
    protected function setup(): void
    {
        DatabaseState::register(\Modules\Activities\Database\State\EnsureActivityTypesArePresent::class);
        DefaultSettings::add('send_contact_attends_to_activity_mail', false);
        DefaultSettings::add('add_event_guests_to_contacts', true);
        DefaultSettings::addRequired('default_activity_type');
        Menu::metric(new TodaysActivitiesMetric);

        SystemInfo::register('PREFERRED_DEFAULT_HOUR', $this->app['config']->get('activities.defaults.hour'));
        SystemInfo::register('PREFERRED_DEFAULT_MINUTES', $this->app['config']->get('activities.defaults.minutes'));

        $this->registerRelatedRecordsDetailTab();
    }

    /**
     * Register module commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            SendOverdueNotifications::class,
            SyncNextActivityDate::class,
        ]);
    }

    /**
     * Schedule module tasks.
     */
    protected function scheduleTasks(Schedule $schedule): void
    {
        $schedule->safeCommand('activities:notify')
            ->name('notify-due-activities')
            ->everyMinute()
            ->withoutOverlapping(5);

        $schedule->safeCommand('activities:sync-next-date')
            ->name('sync-next-activity')
            ->everyFiveMinutes()
            ->withoutOverlapping(5);
    }

    /**
     * Register module related tabs.
     */
    protected function registerRelatedRecordsDetailTab(): void
    {
        $tab = Tab::make('activities', 'activities-tab')->panel('activities-tab-panel')->order(15);

        foreach (['contacts', 'companies', 'deals'] as $resourceName) {
            if ($resource = Innoclapps::resourceByName($resourceName)) {
                $resource->getDetailPage()->tab($tab);
            }
        }
    }

    /**
     * Provide the data to share on the front-end.
     */
    protected function scriptData(): Closure
    {
        return fn () => Auth::check() ? [
            'activities' => [
                'defaults' => config('activities.defaults'),
                'default_activity_type_id' => ActivityType::getDefaultType(),

                'types' => ActivityTypeResource::collection(
                    ActivityType::withCommon()->orderBy('name')->get()
                ),
            ],
        ] : [];
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Activities';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'activities';
    }
}
