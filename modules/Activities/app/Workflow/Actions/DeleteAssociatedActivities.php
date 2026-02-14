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

namespace Modules\Activities\Workflow\Actions;

use Modules\Core\Workflow\Action;

class DeleteAssociatedActivities extends Action
{
    /**
     * Action name
     */
    public static function name(): string
    {
        return __('deals::deal.workflows.actions.delete_associated_activities');
    }

    /**
     * Run the trigger.
     *
     * @return void
     */
    public function run()
    {
        $this->model->incompleteActivities->each->delete();
    }

    /**
     * Action available fields
     */
    public function fields(): array
    {
        return [];
    }
}
