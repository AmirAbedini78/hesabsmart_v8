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

namespace Modules\MailClient\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Common\OAuth\Events\OAuthAccountConnected;
use Modules\Core\Common\OAuth\Events\OAuthAccountDeleting;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Facades\Menu;
use Modules\Core\Fields\Email;
use Modules\Core\Menu\MenuItem;
use Modules\Core\Pages\Tab;
use Modules\Core\Support\ModuleServiceProvider;
use Modules\Core\SystemInfo;
use Modules\MailClient\Client\ClientManager;
use Modules\MailClient\Client\ConnectionType;
use Modules\MailClient\Client\FolderType;
use Modules\MailClient\Console\Commands\PruneStaleScheduledEmails;
use Modules\MailClient\Console\Commands\SendScheduledEmails;
use Modules\MailClient\Console\Commands\SyncEmailAccounts;
use Modules\MailClient\Criteria\EmailAccountsForUserCriteria;
use Modules\MailClient\Listeners\CreateEmailAccountViaOAuth;
use Modules\MailClient\Listeners\StopRelatedOAuthEmailAccounts;
use Modules\MailClient\Listeners\TransferMailClientUserData;
use Modules\MailClient\Models\EmailAccount;
use Modules\MailClient\Models\EmailAccountMessage;
use Modules\Users\Events\TransferringUserData;

class MailClientServiceProvider extends ModuleServiceProvider
{
    protected array $resources = [
        \Modules\MailClient\Resources\EmailMessage::class,
    ];

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(OAuthAccountConnected::class, CreateEmailAccountViaOAuth::class);
        $this->app['events']->listen(OAuthAccountDeleting::class, StopRelatedOAuthEmailAccounts::class);
        $this->app['events']->listen(TransferringUserData::class, TransferMailClientUserData::class);

        $this->commands([
            SyncEmailAccounts::class,
            SendScheduledEmails::class,
            PruneStaleScheduledEmails::class,
        ]);
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
        $this->registerPermissions();
        $this->registerRelatedRecordsDetailTab();

        Email::useDetailComponent('detail-email-sendable-field');
        Email::useIndexComponent('index-email-sendable-field');
        SystemInfo::register('MAIL_CLIENT_SYNC_INTERVAL', $this->app['config']->get('mailclient.sync.interval'));
    }

    /**
     * Schedule module tasks.
     */
    protected function scheduleTasks(Schedule $schedule): void
    {
        $schedule->safeCommand('mailclient:sync --broadcast --isolated=5')
            ->cron($this->app['config']->get('mailclient.sync.interval'))
            ->name('sync-email-accounts')
            ->withoutOverlapping(30)
            ->sendOutputTo(storage_path('logs/email-accounts-sync.log'));

        $schedule->safeCommand('mailclient:prune-failed')
            ->daily()
            ->name('prune-failed-scheduled-emails');

        $schedule->safeCommand('mailclient:send-scheduled')
            ->everyThreeMinutes()
            ->name('send-scheduled-emails');
    }

    /**
     * Provide the data to share on the front-end.
     */
    protected function scriptData(): array
    {
        return ['mail' => [
            'tags_type' => EmailAccountMessage::TAGS_TYPE,
            'reply_prefix' => config('mailclient.reply_prefix'),
            'forward_prefix' => config('mailclient.forward_prefix'),
            'accounts' => [
                'connections' => ConnectionType::cases(),
                'encryptions' => ClientManager::ENCRYPTION_TYPES,
                'from_name' => EmailAccount::DEFAULT_FROM_NAME_HEADER,
            ],
            'folders' => [
                'outgoing' => FolderType::outgoingTypes(),
                'incoming' => FolderType::incomingTypes(),
                'other' => FolderType::OTHER,
                'drafts' => FolderType::DRAFTS,
            ],
        ],
        ];
    }

    /**
     * Provide the main menu items.
     */
    protected function menu(): MenuItem
    {
        $accounts = auth()->check() ? EmailAccount::with('oAuthAccount')
            ->criteria(EmailAccountsForUserCriteria::class)
            ->get()->filter->canSendEmail() : null;

        return MenuItem::make(__('mailclient::inbox.inbox'), '/inbox')
            ->icon('Inbox')
            ->position(15)
            ->badge(fn () => EmailAccount::countUnreadMessagesForUser(Auth::user()))
            ->inQuickCreate(! is_null($accounts?->filter->isPrimary()->first() ?? $accounts?->first()))
            ->quickCreateName(__('mailclient::mail.send'))
            ->quickCreateRoute('/inbox?compose=true')
            ->keyboardShortcutChar('E')
            ->badgeVariant('info');
    }

    /**
     * Register the mail client module permissions.
     */
    protected function registerPermissions(): void
    {
        Innoclapps::permissions(function ($manager) {
            $manager->group(['name' => 'inbox', 'as' => __('mailclient::inbox.shared')], function ($manager) {
                $manager->view('access-inbox', [
                    'as' => __('core::role.capabilities.access'),
                    'permissions' => [
                        'access shared inbox' => __('core::role.capabilities.access'),
                    ],
                ]);
            });
        });
    }

    /**
     * Register the documents module related tabs.
     */
    protected function registerRelatedRecordsDetailTab(): void
    {
        $tab = Tab::make('emails', 'emails-tab')->panel('emails-tab-panel')->order(20);

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
        return 'MailClient';
    }

    /**
     * Provide the module name in lowercase.
     */
    protected function moduleNameLower(): string
    {
        return 'mailclient';
    }
}
