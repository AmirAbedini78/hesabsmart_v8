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
use Modules\Updater\UpdateFinalizer;

class FinalizeUpdateController extends Controller
{
    /**
     * Show the update finalization action.
     */
    public function show(UpdateFinalizer $finalizer): RedirectResponse|View
    {
        if (! $finalizer->needed()) {
            return redirect('/dashboard');
        }

        return view('updater::finalize');
    }

    /**
     * Perform update finalization.
     */
    public function finalize(UpdateFinalizer $finalizer): void
    {
        abort_unless($finalizer->needed(), 404);

        $finalizer->run();
    }
}
