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

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PrivacyPolicy extends Controller
{
    /**
     * Display the privacy policy.
     */
    public function __invoke(): View
    {
        $content = clean(settings('privacy_policy'));

        return view('core::privacy-policy', compact('content'));
    }
}
