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

namespace Modules\MailClient\Filters;

use Modules\Core\Filters\HasFilter;
use Modules\Core\Filters\Number;
use Modules\Core\Filters\Operand;

class ResourceEmailsFilter extends HasFilter
{
    /**
     * Initialize ResourceEmailsFilter class
     */
    public function __construct()
    {
        parent::__construct('emails', __('mailclient::inbox.inbox'));

        $this->setOperands([
            Operand::from(
                Number::make('total_unread', __('mailclient::inbox.unread_count'))->countFromRelation('unreadEmailsForUser')
            ),
        ]);
    }
}
