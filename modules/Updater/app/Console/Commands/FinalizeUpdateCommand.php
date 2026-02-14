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
use Modules\Updater\UpdateFinalizer;

class FinalizeUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updater:finalize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalize the application recent update.';

    /**
     * Execute the console command.
     */
    public function handle(UpdateFinalizer $finalizer): void
    {
        if (! $finalizer->needed()) {
            $this->info('There is no update to finalize.');
        } else {
            $finalizer->run();

            $this->info('The update has been finalized.');
        }
    }
}
