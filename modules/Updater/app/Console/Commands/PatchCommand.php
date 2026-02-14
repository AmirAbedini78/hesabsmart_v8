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
use Modules\Core\Facades\Innoclapps;
use Modules\Installer\RequirementsChecker;
use Modules\Updater\Patcher;
use Modules\Users\Models\User;
use Throwable;

class PatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updater:patch {--key= : Purchase key} {--force} {--critical} {--delete-source=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply any available patches.';

    /**
     * Execute the console
     */
    public function handle(RequirementsChecker $requirements, Patcher $patcher): int
    {
        if ($requirements->fails('zip')) {
            $this->error(__('updater::update.patch_zip_is_required'));

            return Command::FAILURE;
        }

        $this->info('Configuring purchase key.');

        $patcher->usePurchaseKey($this->option('key') ?: '');

        if ($this->getLaravel()->runningInConsole() && empty($patcher->getPurchaseKey())) {
            $this->error('Purchase key empty.');

            return Command::FAILURE;
        }

        $force = $this->option('force');
        $deleteSource = filter_var($this->option('delete-source'), FILTER_VALIDATE_BOOL);

        $patches = $patcher->getAvailablePatches()->reject->isApplied();

        if ($this->option('critical')) {
            $patches = $patches->filter->isCritical();
        }

        if ($patches->isEmpty()) {
            $this->info('No patches available for the current installation version.');

            return Command::FAILURE;
        }

        if (! $force && User::anyActiveRecently()) {
            $this->info('Skipping patching, the last active user was in less than 30 minutes, try later.');

            return Command::FAILURE;
        }

        $this->down();

        try {
            foreach ($patches as $patch) {
                $this->info('Applying patch with token: '.$patch->token());
                $patcher->apply($patcher->fetch($patch), $deleteSource);
            }

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->up();

            throw $e;
        } finally {
            $this->up();

            if (config('updater.restart_queue')) {
                $this->info('Restarting queue.');
                Innoclapps::restartQueue();
            }
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
        $this->callSilently('down', ['--render' => 'updater::errors.patching']);
    }
}
