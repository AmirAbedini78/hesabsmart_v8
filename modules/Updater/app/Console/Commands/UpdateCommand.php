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

namespace Modules\Updater\Console\Commands;

use Illuminate\Console\Command;
use Modules\Installer\RequirementsChecker;
use Modules\Updater\Updater;
use Modules\Users\Models\User;
use Throwable;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updater:update {--key= : Purchase key} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the application to the latest available version.';

    /**
     *  Execute the console command.
     */
    public function handle(RequirementsChecker $requirements, Updater $updater): int
    {
        if ($requirements->fails('zip')) {
            $this->error(__('updater::update.update_zip_is_required'));

            return Command::FAILURE;
        }

        $this->info('Configuring purchase key.');

        $updater->usePurchaseKey($this->option('key') ?: '');

        if ($this->getLaravel()->runningInConsole() && empty($updater->getPurchaseKey())) {
            $this->error('Purchase key empty.');

            return Command::FAILURE;
        }

        if (! $updater->isNewVersionAvailable()) {
            $this->info('The latest version '.$updater->getVersionInstalled().' is already installed.');

            return Command::FAILURE;
        }

        $force = $this->option('force');

        if (! $force && User::anyActiveRecently()) {
            $this->info('Skipping update, the last active user was in less than 30 minutes, try later.');

            return Command::FAILURE;
        }

        $this->info('Preparing update.');

        $this->down();

        if (! $this->getLaravel()->runningUnitTests()) {
            $this->info('Increasing PHP config values.');
            $updater->increasePhpIniValues();
        }

        $this->info('Performing update, this may take a while.');

        try {
            $updater->update($updater->getVersionAvailable());

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->up();

            throw $e;
        } finally {
            $this->up();
        }

        return Command::FAILURE;
    }

    /**
     * Bring the application out of maintenance mode
     */
    protected function up(): void
    {
        $this->info('Bringing the application out of maintenance mode.');
        $this->callSilently('up');
    }

    /**
     * Put the application into maintenance mode
     */
    protected function down(): void
    {
        $this->info('Putting the application into maintenance mode.');
        $this->callSilently('down', ['--render' => 'updater::errors.updating']);
    }
}
