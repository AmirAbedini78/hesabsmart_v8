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

namespace Modules\Core\Providers;

use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Application;
use Modules\Core\Common\Media\PruneStaleMediaAttachments;
use Modules\Core\Common\Synchronization\Jobs\PeriodicSynchronizations;
use Modules\Core\Common\Synchronization\Jobs\RefreshWebhookSynchronizations;
use Modules\Core\Common\Timeline\Timelineables;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Environment;
use Modules\Core\Facades\ChangeLogger;
use Modules\Core\Facades\Fields;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\MailableTemplates;
use Modules\Core\Facades\Menu;
use Modules\Core\Facades\ReCaptcha;
use Modules\Core\Facades\SettingsMenu;
use Modules\Core\Facades\Zapier;
use Modules\Core\Http\Resources\TagResource;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Models\Tag;
use Modules\Core\Notification;
use Modules\Core\Resource\Resource;
use Modules\Core\Settings\DefaultSettings;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Tool;
use Modules\Core\Workflow\WorkflowEventsSubscriber;
use Modules\Core\Workflow\Workflows;
use Modules\Installer\Events\InstallationSucceeded;
use Modules\Updater\Events\PatchApplied;
use Modules\Updater\Events\UpdateFinalized;

class CoreServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerTranslations();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerEvents();
        $this->registerDefaultSettings();

        Innoclapps::whenReadyForServing(Timelineables::discover(...));

        $this->registerMenuItems();
        $this->registerSettingsMenuItems();

        DatabaseState::register([
            \Modules\Core\Database\State\EnsureMailableTemplatesAreSeeded::class,
            \Modules\Core\Database\State\EnsureDefaultSettingsArePresent::class,
            \Modules\Core\Database\State\EnsureCountriesArePresent::class,
        ]);

        $this->app->booted(function () {
            $this->listenToWorkflowEvents();
            $this->scheduleTasks();
            $this->registerTools();

            $this->bootCore();
        });
    }

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Boot the core features.
     */
    protected function bootCore(): void
    {
        Innoclapps::provideToScript($this->shareDataToScriptCallback());
    }

    /**
     * Register module migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
    }

    /**
     * Register module views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(
            module_path($this->moduleName, 'resources/views'),
            $this->moduleNameLower
        );
    }

    /**
     * Register module translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(
            module_path($this->moduleName, 'lang'),
            $this->moduleNameLower
        );
    }

    /**
     * Register the menu items.
     */
    protected function registerMenuItems(): void
    {
        Menu::register(function () {
            return [
                MenuItem::make(__('core::dashboard.insights'), '/dashboard')
                    ->icon('ChartSquareBar')
                    ->position(40),
                MenuItem::make(__('core::settings.settings'), '/settings')
                    ->icon('Cog')
                    ->position(100)
                    ->canSeeWhen('is-super-admin'),
            ];
        });
    }

    /**
     * Register the core settings menu items.
     */
    protected function registerSettingsMenuItems(): void
    {
        SettingsMenu::register(function () {
            return [
                SettingsMenuItem::make('integrations', __('core::app.integrations'), [
                    SettingsMenuItem::make('pusher', __('core::integration.pusher'))->path('/integrations/pusher'),
                    SettingsMenuItem::make('microsoft', __('core::integration.microsoft'))->path('/integrations/microsoft'),
                    SettingsMenuItem::make('google', __('core::integration.google'))->path('/integrations/google'),
                    SettingsMenuItem::make('zapier', __('core::integration.zapier'))->path('/integrations/zapier'),
                ])->icon('Globe')->order(20),

                SettingsMenuItem::make('security', __('core::settings.security.security'), [
                    SettingsMenuItem::make('security', __('core::settings.general'))->path('/security'),
                    SettingsMenuItem::make('recaptcha', __('core::settings.recaptcha.recaptcha'))->path('/recaptcha'),
                ])->icon('ShieldCheck')->order(60),

                SettingsMenuItem::make('system', __('core::settings.system'), [
                    SettingsMenuItem::make('tools', __('core::settings.tools.tools'))->path('/tools'),
                    SettingsMenuItem::make('system-info', __('core::app.system_info'))->path('/info'),
                    SettingsMenuItem::make('system-logs', 'Logs')->path('/logs'),
                ])->icon('Cog')->order(70),

                SettingsMenuItem::make('workflows', __('core::workflow.workflows'))
                    ->path('/workflows')
                    ->icon('RocketLaunch')
                    ->order(40),

                SettingsMenuItem::make('mailable-templates', __('core::mail_template.mail_templates'))
                    ->path('/mailable-templates')
                    ->icon('Mail')
                    ->order(50),

                SettingsMenuItem::make('modules', __('core::modules.modules'))
                    ->path('/modules')
                    ->icon('ArrowPathRoundedSquare')
                    ->order(55),
            ];
        });

        SettingsMenu::register(function () {
            $item = SettingsMenuItem::make('fields', __('core::fields.fields'))->icon('SquaresPlus')->order(10);

            Innoclapps::registeredResources()
                ->filter(fn ($resource) => $resource::$fieldsCustomizable)
                ->each(function (Resource $resource) use ($item) {
                    $item->withChildren(
                        SettingsMenuItem::make($resource->name(), $resource->singularLabel())->path("/fields/{$resource->name()}")
                    );
                });

            return $item;
        });
    }

    /**
     * Register the core commands.
     */
    public function registerCommands(): void
    {
        $this->commands([
            \Modules\Core\Console\Commands\OptimizeCommand::class,
            \Modules\Core\Console\Commands\ClearCacheCommand::class,
            \Modules\Core\Console\Commands\ClearExcelTmpPathCommand::class,
            \Modules\Core\Console\Commands\ClearHtmlPurifierCacheCommand::class,
            \Modules\Core\Console\Commands\GenerateIdentificationKeyCommand::class,
        ]);
    }

    /**
     * Schedule the document related tasks.
     */
    protected function scheduleTasks(): void
    {
        /** @var \Illuminate\Console\Scheduling\Schedule */
        $schedule = $this->app->make(Schedule::class);

        $schedule->call(Environment::captureCron(...))
            ->name('core:capture-cron-environment')
            ->everyMinute();

        $schedule->call(new PruneStaleMediaAttachments)
            ->name('core:prune-stale-media-attachments')
            ->daily();

        $schedule->job(PeriodicSynchronizations::class)
            ->cron($this->app['config']->get('core.synchronization.interval'))
            ->name('core:synchronization-run-periodic');

        $schedule->job(RefreshWebhookSynchronizations::class)
            ->name('core:synchronization-run-webhook-refresh')
            ->daily();

        $schedule->safeCommand('model:prune')->daily();
        $schedule->safeCommand('queue:flush')->weekly();
    }

    /**
     * Listen to workflow events.
     */
    protected function listenToWorkflowEvents(): void
    {
        // Must be called before registering the "WorkflowEventsSubscriber" subscriber.
        Workflows::registerEventOnlyTriggersListeners();

        $this->app['events']->subscribe(WorkflowEventsSubscriber::class);
    }

    /**
     * Register the core event listeners.
     */
    protected function registerEvents(): void
    {
        $this->app['events']->listen(RequestHandled::class, Workflows::processQueue(...));
        $this->app['events']->listen(RequestHandled::class, Zapier::processQueue(...));

        $this->app['events']->listen(
            [PatchApplied::class, UpdateFinalized::class],
            MailableTemplates::seed(...)
        );

        $this->app['events']->listen(InstallationSucceeded::class, function () {
            ChangeLogger::disabled(fn () => DatabaseState::seed());
        });
    }

    /**
     * Register the core tools.
     */
    protected function registerTools(): void
    {
        Innoclapps::tools(function () {
            return [
                Tool::new('clear-cache', function () {
                    Innoclapps::clearCache();
                    Innoclapps::restartQueue();
                })->description(__('core::settings.tools.clear-cache')),

                Tool::new('optimize', function () {
                    Innoclapps::optimize();
                    Innoclapps::restartQueue();
                })->description(__('core::settings.tools.optimize')),

                Tool::new('storage-link', Innoclapps::createStorageLink(...))->description(__('core::settings.tools.storage-link')),

                Tool::new('seed-mailable-templates', MailableTemplates::seed(...))->description(__('core::settings.tools.seed-mailable-templates')),
            ];
        });
    }

    /**
     * Register the default settings.
     */
    protected function registerDefaultSettings(): void
    {
        DefaultSettings::add('disable_password_forgot', false);
        DefaultSettings::addRequired('date_format', 'F j, Y');
        DefaultSettings::addRequired('time_format', 'H:i');
        DefaultSettings::add('block_bad_visitors', false);
        DefaultSettings::addRequired('currency', 'USD');
        DefaultSettings::addRequired(
            'allowed_extensions',
            'jpg, jpeg, png, gif, svg, pdf, aac, ogg, oga, mp3, wav, mp4, m4v,mov, ogv, webm, zip, rar, doc, docx, txt, text, xml, json, xls, xlsx, odt, csv, ppt, pptx, ppsx, ics, eml'
        );
    }

    /**
     * Get the core callback for sharing data to script.
     */
    protected function shareDataToScriptCallback(): Closure
    {
        return function () {
            /** @var \Modules\Users\Models\User */
            $user = Auth::user();

            $data = [];

            $config = $this->app['config'];

            $data['apiURL'] = Application::apiUrl();
            $data['url'] = Application::url();
            $data['locale'] = app()->getLocale();
            $data['locales'] = collect(Application::locales())->map(function ($locale) {
                $label = config("locales.$locale", $locale);

                if ($label !== $locale) {
                    $label = $locale.' - '.$label;
                }

                return ['value' => $locale, 'label' => $label];
            })->values()->all();
            $data['fallback_locale'] = $config->get('app.fallback_locale');
            $data['timezone'] = $config->get('app.timezone');
            $data['is_secure'] = request()->secure();
            $data['defaults'] = $config->get('core.defaults');
            $data['demo'] = $config->get('demo.enabled');
            $data['csrfToken'] = csrf_token();

            if (Application::requiresMaintenance()) {
                return $data;
            }

            $data['broadcasting'] = [
                'default' => $config->get('broadcasting.default'),
                'connection' => $config->get('broadcasting.connections.'.$config->get('broadcasting.default')),
            ];

            $data['time_format'] = $config->get('core.time_format');
            $data['date_format'] = $config->get('core.date_format');
            $data['company_name'] = $config->get('app.name');
            $data['logo_light'] = $config->get('core.logo.light');
            $data['logo_dark'] = $config->get('core.logo.dark');
            $data['disable_password_forgot'] = forgot_password_is_disabled();

            $data['max_upload_size'] = $config->get('mediable.max_size');
            $data['privacyPolicyUrl'] = privacy_url();

            $data['date_formats'] = $config->get('core.date_formats');
            $data['time_formats'] = $config->get('core.time_formats');

            $data['currency'] = with(Application::currency(), function ($currency) {
                return array_merge(
                    $currency->toArray()[$isoCode = $currency->getCurrency()],
                    ['iso_code' => $isoCode]
                );
            });

            $data['reCaptcha'] = [
                'configured' => ReCaptcha::configured(),
                'validate' => ReCaptcha::shouldShow(),
                'siteKey' => ReCaptcha::getSiteKey(),
            ];

            // Required in FormField Group for externals forms e.q. web form
            $data['fields'] = [
                'views' => [
                    'index' => Fields::INDEX_VIEW,
                    'create' => Fields::CREATE_VIEW,
                    'detail' => Fields::DETAIL_VIEW,
                    'update' => Fields::UPDATE_VIEW,
                ],
            ];

            // Authenticated user config
            if ($user) {
                $data['version'] = Application::VERSION;
                $data['environment'] = $this->app->environment();

                if ($user->isSuperAdmin()) {
                    $data['purchase_key'] = $config->get('app.purchase_key');
                    $data['tools'] = Application::getTools();
                }

                $data['resources'] = Application::registeredResources()->mapWithKeys(
                    fn (Resource $resource) => [$resource->name() => $resource]
                );

                $data['tags'] = TagResource::collection(Tag::get());

                $data['fields'] = array_merge($data['fields'], [
                    'custom_fields' => Fields::customFieldable(),
                    'custom_field_prefix' => $config->get('fields.custom_fields.prefix'),
                ]);

                $data['mailable_templates'] = [
                    'can_send_via_mailer' => $config->get('demo.enabled', ! in_array($config->get('mail.default'), ['array', 'log'])),
                    'can_send_via_mail_client' => $config->get('demo.enabled', (bool) settings('system_email_account_id')),
                ];

                $data['menu'] = [
                    'sidebar' => Menu::get(),
                    'metrics' => Menu::metrics(),
                    'settings' => SettingsMenu::all(),
                ];

                $data['notifications_settings'] = Notification::preferences();

                $data['soft_deletes'] = [
                    'prune_after' => $config->get('core.soft_deletes.prune_after'),
                ];

                $data['views'] = [
                    'max_open' => $config->get('core.views.max_open'),
                ];

                $data['contentbuilder'] = [
                    'fonts' => $config->get('contentbuilder.fonts'),
                ];

                $data['integrations'] = [
                    'microsoft' => [
                        'client_id' => $config->get('integrations.microsoft.client_id'),
                    ],
                    'google' => [
                        'client_id' => $config->get('integrations.google.client_id'),
                    ],
                ];

                $data['favourite_colors'] = Application::favouriteColors();
            }

            return $data;
        };
    }
}
