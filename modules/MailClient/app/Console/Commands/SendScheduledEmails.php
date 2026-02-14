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

namespace Modules\MailClient\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Modules\MailClient\Models\ScheduledEmail;

class SendScheduledEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailclient:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email messages scheduled to send later.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        ScheduledEmail::query()
            ->where(function (Builder $query) {
                $query->dueForSend();
            })
            ->orWhere(function (Builder $query) {
                $query->retryable(Carbon::asAppTimezone());
            })
            ->with(['media', 'account.sentFolder', 'account.oAuthAccount'])
            ->get()
            ->each(fn (ScheduledEmail $message) => $message->markAsSending())
            ->each(function (ScheduledEmail $message) {
                $message->send();
            });
    }
}
