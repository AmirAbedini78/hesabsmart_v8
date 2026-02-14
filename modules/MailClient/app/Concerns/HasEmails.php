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

namespace Modules\MailClient\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Core\Models\Model;
use Modules\MailClient\Criteria\EmailAccountsForUserCriteria;

/** @mixin \Modules\Core\Models\Model */
trait HasEmails
{
    /**
     * Boot the HasEmails trait.
     */
    protected static function bootHasEmails(): void
    {
        static::deleted(function (Model $model) {
            $model->scheduledEmails()->delete();
        });
    }

    /**
     * Get all of the scheduled emails for the model.
     */
    public function scheduledEmails(): MorphToMany
    {
        return $this->morphToMany(
            \Modules\MailClient\Models\ScheduledEmail::class,
            'model',
            'model_has_scheduled_emails',
            null,
            'scheduled_email_id'
        );
    }

    /**
     * Get all of the emails for the model.
     */
    public function emails(): MorphToMany
    {
        return $this->morphToMany(
            \Modules\MailClient\Models\EmailAccountMessage::class,
            'messageable',
            'email_account_messageables',
            null,
            'message_id'
        );
    }

    /**
     * A model has unread emails
     */
    public function unreadEmails(): MorphToMany
    {
        return $this->emails()->unread()->whereHas('folders', function ($folderQuery) {
            return $folderQuery->where('syncable', true);
        });
    }

    /**
     * Get the unread emails that the user can see
     */
    public function unreadEmailsForUser(): MorphToMany
    {
        return $this->unreadEmails()->where(function ($query) {
            $query->whereHas('account', function ($accountQuery) {
                $accountQuery->criteria(EmailAccountsForUserCriteria::class);
            })->whereHas('folders.account', function ($query) {
                return $query->whereColumn('folder_id', '!=', 'trash_folder_id');
            });
        });
    }
}
