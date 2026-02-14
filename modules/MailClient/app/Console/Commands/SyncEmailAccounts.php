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

namespace Modules\MailClient\Console\Commands;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Broadcast;
use Modules\MailClient\Models\EmailAccount;
use Modules\MailClient\Synchronization\EmailAccountSynchronizationManager;

class SyncEmailAccounts extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailclient:sync
                        {--account= : Email account ID}
                        {--broadcast : Whether to broadcast events}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes email accounts.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Gathering email accounts to sync.');

        $accounts = $this->getAccounts();

        if ($accounts->isEmpty()) {
            $this->info('No accounts found for synchronization.');
        } else {
            $this->info(sprintf('Performing sync for %d email accounts.', $accounts->count()));
        }

        $this->sync($accounts);
    }

    /**
     * Sync the email accounts.
     */
    protected function sync(Collection $accounts): void
    {
        $synced = false;

        // When the "inital sync from" option "now" is selected and the sync runs for first time
        // and if nothing is synchronized the UI message that initial sync is not performed won't be removed
        // In this case, will make sure to broadcast so the accounts are refetched
        $hasInitialSync = false;

        foreach ($accounts as $account) {
            if (! $account->isInitialSyncPerformed()) {
                $hasInitialSync = true;
            }

            $this->info(sprintf('Starting synchronization for account %s.', $account->email));

            $synchronizer = EmailAccountSynchronizationManager::getSynchronizer($account)->setCommand($this);

            if ($synchronizer->perform()) {
                $synced = true;
            }
        }

        if ($this->option('broadcast') && ($synced || $hasInitialSync)) {
            // https://github.com/laravel/framework/pull/51082
            Broadcast::on(new PrivateChannel('inbox.emails'))->as('synchronized')->send();
        }
    }

    /**
     * Get the accounts that should be synced.
     */
    protected function getAccounts(): Collection
    {
        $accounts = EmailAccount::with(['oAuthAccount', 'folders', 'user'])
            ->syncable()
            ->orderBy('email')
            ->get();

        if ($this->option('account')) {
            $accounts = $accounts->filter(function (EmailAccount $account) {
                return (int) $account->id === (int) $this->option('account');
            })->values();
        }

        return $accounts;
    }
}
