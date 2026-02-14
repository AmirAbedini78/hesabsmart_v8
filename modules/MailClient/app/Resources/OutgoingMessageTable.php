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

namespace Modules\MailClient\Resources;

use Modules\Core\Table\Column;
use Modules\Core\Table\DateTimeColumn;
use Modules\Core\Table\HasManyColumn;
use Modules\MailClient\Models\EmailAccountMessage;
use Modules\MailClient\Models\EmailAccountMessageAddress;

class OutgoingMessageTable extends IncomingMessageTable
{
    /**
     * Provides table available default columns
     */
    public function columns(): array
    {
        return [
            Column::make('subject', __('mailclient::inbox.subject'))->width('470px')->minWidth('470px')->doNotAllowVisibilityToggle(),

            HasManyColumn::make('to', 'address', __('mailclient::inbox.to'))
                ->select('name')
                ->fillRowDataUsing(function (array &$row, EmailAccountMessage $message) {
                    $row['to'] = $message->to->map(
                        fn (EmailAccountMessageAddress $to) => ['address' => $to->address, 'name' => $to->name]
                    );
                })->doNotAllowVisibilityToggle(),

            DateTimeColumn::make('date', __('mailclient::inbox.date'))->doNotAllowVisibilityToggle(),
        ];
    }
}
