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

namespace Modules\Calls\Providers;

use Closure;
use Illuminate\Support\Facades\Auth;
use Modules\Calls\Http\Resources\CallOutcomeResource;
use Modules\Calls\Listeners\TransferCallsUserData;
use Modules\Calls\Models\CallOutcome;
use Modules\Calls\VoIP\VoIP;
use Modules\Contacts\Fields\Phone;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\SettingsMenu;
use Modules\Core\Pages\Tab;
use Modules\Core\Settings\ConfigOverrides;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Core\Workflow\Workflows;
use Modules\Users\Events\TransferringUserData;

class CallsServiceProvider extends ModuleServiceProvider
{
    protected array $resources = [
        \Modules\Calls\Resources\Call::class,
        \Modules\Calls\Resources\CallOutcome::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(TransferringUserData::class, TransferCallsUserData::class);
    }

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register module config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName(), 'config/config.php'),
            $this->moduleNameLower()
        );

        $this->mergeConfigFrom(
            module_path($this->moduleName(), 'config/twilio.php'),
            'twilio'
        );

        $this->mergeConfigFrom(
            module_path($this->moduleName(), 'config/voip.php'),
            'voip'
        );
    }

    /**
     * Configure the module.
     */
    protected function setup(): void
    {
        $this->registerWorkflowTriggers();
        $this->registerRelatedRecordsDetailTab();

        DatabaseState::register(
            \Modules\Calls\Database\State\EnsureCallOutcomesArePresent::class
        );

        Phone::useDetailComponent('detail-phone-callable-field');
        Phone::useIndexComponent('index-phone-callable-field');

        ConfigOverrides::add([
            'twilio.applicationSid' => 'twilio_app_sid',
            'twilio.accountSid' => 'twilio_account_sid',
            'twilio.authToken' => 'twilio_auth_token',
            'twilio.number' => 'twilio_number',
        ]);

        Innoclapps::whenReadyForServing(function () {
            $this->configureVoIP();
        });
    }

    /**
     * Register the module settings menu items.
     */
    protected function registerSettingsMenuItems(): void
    {
        SettingsMenu::add(
            'integrations', fn () => SettingsMenuItem::make('twilio', __('calls::twilio.twilio'))->path('/integrations/twilio')
        );
    }

    /**
     * Set the application VoIP Client
     */
    protected function configureVoIP(): void
    {
        $options = $this->app['config']->get('twilio');

        $totalFilled = count(array_filter($options));

        if ($totalFilled === count($options)) {
            $this->app['config']->set('voip.client', 'twilio');

            Innoclapps::permissions(function ($manager) {
                $manager->group(['name' => 'voip', 'as' => __('calls::call.voip_permissions')], function ($manager) {
                    $manager->view('view', [
                        'as' => __('calls::call.capabilities.use_voip'),
                        'permissions' => ['use voip' => __('calls::call.capabilities.use_voip')],
                    ]);
                });
            });
        }
    }

    /**
     * Register the module workflow triggers.
     */
    protected function registerWorkflowTriggers(): void
    {
        Workflows::triggers([
            \Modules\Calls\Workflow\Triggers\MissedIncomingCall::class,
        ]);
    }

    /**
     * Provide the data to share on the front-end.
     */
    protected function scriptData(): Closure
    {
        return fn () => Auth::check() ? [
            'voip' => [
                'client' => config('voip.client'),
                'endpoints' => [
                    'call' => VoIP::callUrl(),
                    'events' => VoIP::eventsUrl(),
                ],
            ],
            'calls' => [
                'outcomes' => CallOutcomeResource::collection(CallOutcome::orderBy('name')->get()),
            ],
        ] : [];
    }

    /**
     * Register the module related tabs.
     */
    protected function registerRelatedRecordsDetailTab(): void
    {
        $tab = Tab::make('calls', 'calls-tab')->panel('calls-tab-panel')->order(30);

        foreach (['contacts', 'companies', 'deals'] as $resourceName) {
            if ($resource = Innoclapps::resourceByName($resourceName)) {
                $resource->getDetailPage()->tab($tab);
            }
        }
    }

    /**
     * Provide the module name.
     */
    protected function moduleName(): string
    {
        return 'Calls';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'calls';
    }
}
