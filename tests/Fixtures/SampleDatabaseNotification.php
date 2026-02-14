<?php

namespace Tests\Fixtures;

use Modules\Core\Notification;

class SampleDatabaseNotification extends Notification
{
    /**
     * Get the notification available delivery channels
     */
    public static function channels(): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [

        ];
    }
}
