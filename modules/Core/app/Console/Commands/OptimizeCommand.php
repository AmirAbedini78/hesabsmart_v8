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
use Modules\Core\Fields\CustomFieldFileCache;

class OptimizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the application by caching bootstrap files.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('optimize');

        collect([
            'custom-fields' => fn () => CustomFieldFileCache::refresh(),
        ])->each(fn ($task, $description) => $this->components->task($description, $task));

        $this->newLine();
    }
}
