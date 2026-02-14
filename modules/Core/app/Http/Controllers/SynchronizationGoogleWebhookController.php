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
use Illuminate\Http\Request;
use Modules\Core\Models\Synchronization;

class SynchronizationGoogleWebhookController extends Controller
{
    /**
     *  Handle the webhook request.
     */
    public function handle(Request $request): void
    {
        if ($request->header('x-goog-resource-state') !== 'exists') {
            return;
        }

        $synchronization = Synchronization::where('resource_id', $request->header('x-goog-resource-id'))
            ->findOrFail($request->header('x-goog-channel-id'));

        $synchronization->ping();
    }
}
