<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Database\State\DatabaseState;
use Modules\Core\Environment;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\CustomFieldFileCache;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Innoclapps::clearCache();
        Innoclapps::muteAllCommunicationChannels();
        CustomFieldFileCache::flush();

        settings(['_seeded' => false]);

        DatabaseState::seed();

        $this->call(DemoDataSeeder::class);

        Environment::setInstallationDate();
    }
}
