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

namespace Modules\Activities\Contracts;

interface Attendeeable
{
    /**
     * Get the person email address
     */
    public function getGuestEmail(): ?string;

    /**
     * Get the person displayable name
     */
    public function getGuestDisplayName(): string;

    /**
     * Get the notification that should be sent to the person when is added as guest
     *
     * @return \Illuminate\Mail\Mailable|\Illuminate\Notifications\Notification|string
     */
    public function getAttendeeNotificationClass();

    /**
     * Indicates whether the attending notification should be send to the guest
     */
    public function shouldSendAttendingNotification(Attendeeable $model): bool;
}
