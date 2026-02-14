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

namespace Modules\Activities\Fields;

use Modules\Core\Fields\DateTime;

class NextActivityDate extends DateTime
{
    /**
     * Initialize new NextActivityDate instance
     */
    public function __construct()
    {
        parent::__construct('next_activity_date', __('activities::activity.next_activity_date'));

        $this->exceptOnForms()
            ->excludeFromDetail()
            ->excludeFromSettings()
            ->excludeFromImport()
            ->readonly(true)
            ->help(__('activities::activity.next_activity_date_info'))
            ->hidden();
    }
}
