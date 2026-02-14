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

namespace Modules\Updater\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Updater\DatabaseMigrator;

class MigrateController extends Controller
{
    /**
     * Show the migration required action.
     */
    public function show(DatabaseMigrator $migrator): RedirectResponse|View
    {
        if (! $migrator->needed()) {
            return redirect('/dashboard');
        }

        return view('updater::migrate');
    }

    /**
     * Perform migration.
     */
    public function migrate(DatabaseMigrator $migrator): void
    {
        abort_unless($migrator->needed(), 404);

        $migrator->run();
    }
}
