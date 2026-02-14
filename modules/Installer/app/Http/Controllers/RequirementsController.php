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

namespace Modules\Installer\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use Modules\Core\Environment;
use Modules\Installer\PermissionsChecker;
use Modules\Installer\RequirementsChecker;

class RequirementsController
{
    /**
     * Shows the requirements page.
     */
    public function show(RequirementsChecker $requirements, PermissionsChecker $permissions): View
    {
        $php = $requirements->checkPHPversion();
        $requirements = $requirements->check();
        $permissions = $permissions->check();

        ViewFacade::share(['withSteps' => false]);

        return view('installer::requirements-checker', [
            'php' => $php,
            'requirements' => $requirements,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Confirm the requirements
     */
    public function confirm(): RedirectResponse
    {
        Environment::capture();

        return redirect()->back();
    }
}
