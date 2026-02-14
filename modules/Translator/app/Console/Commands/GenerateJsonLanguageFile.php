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

namespace Modules\Translator\Console\Commands;

use Illuminate\Console\Command;
use Modules\Translator\Translator;

class GenerateJsonLanguageFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translator:json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate application json language file.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Translator::generateJsonLanguageFile();

        $this->info('Language file generated successfully.');
    }
}
