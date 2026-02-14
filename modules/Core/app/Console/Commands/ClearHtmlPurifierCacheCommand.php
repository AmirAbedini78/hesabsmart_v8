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

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ClearHtmlPurifierCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'html-purifier:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the HTML Purifier cache.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $filesystem): int
    {
        return with($filesystem->deepCleanDirectory(
            $this->laravel['config']->get('html_purifier.cachePath')
        ), function ($result) {
            $this->info('HTML Purifier cache has been flushed.');

            return $result ? Command::SUCCESS : Command::FAILURE;
        });
    }
}
