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
use Modules\Core\Fields\CustomFieldFileCache;
use Modules\Core\Module\ModuleAutoloader;

class ClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the application cache.';

    /**
     * Execute the console command.
     */
    public function handle(Filesystem $filesystem): void
    {
        $this->components->info('Clearing application cache.');

        collect([
            'fonts' => fn () => $filesystem->deepCleanDirectory(
                $this->laravel['config']->get('dompdf.options.font_cache')
            ),
            'module-autoloader' => fn () => ModuleAutoloader::flushCache(),
            'custom-fields' => fn () => CustomFieldFileCache::refresh(),
            'html-purifier' => fn () => $this->callSilent('html-purifier:clear') == 0,
            'model-cache' => fn () => $this->callSilent('modelCache:clear') == 0,
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->newLine();

        $this->call('optimize:clear');
    }
}
