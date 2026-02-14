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

namespace Modules\Core\Common\Synchronization\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Common\Synchronization\Exceptions\InvalidSyncNotificationURLException;
use Modules\Core\Models\Synchronization;

class RefreshWebhookSynchronizations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Synchronization::with('synchronizable')
            ->withoutOAuthAuthenticationRequired()
            ->where(function ($query) {
                return $query->enabled()
                    ->where(function ($query) {
                        return $query->whereNull('expires_at')->orWhere('expires_at', '<', now()->addDays(2));
                    })->whereNotNull('resource_id');
            })->get()->each(function ($synchronization) {
                try {
                    $synchronization->refreshWebhook();
                } catch (InvalidSyncNotificationURLException) {
                    $synchronization->stopSync(
                        'We were unable to verify the notification URL for changes, make sure that your installation is publicly accessible, your installation URL starts with "https" and using valid SSL certificate.'
                    );
                }
            });
    }
}
