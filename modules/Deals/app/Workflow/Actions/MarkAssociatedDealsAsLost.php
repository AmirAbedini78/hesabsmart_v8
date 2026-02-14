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

namespace Modules\Deals\Workflow\Actions;

use Modules\Core\Facades\ChangeLogger;
use Modules\Core\Workflow\Action;
use Modules\Deals\Fields\LostReasonField;
use Modules\Deals\Models\Deal;

class MarkAssociatedDealsAsLost extends Action
{
    /**
     * Initialize MarkAssociatedDealsAsLost
     */
    public function __construct(protected string $relation) {}

    /**
     * Action name
     */
    public static function name(): string
    {
        return __('deals::deal.workflows.actions.mark_associated_deals_as_lost');
    }

    /**
     * Run the trigger
     */
    public function run()
    {
        ChangeLogger::setCauser($this->workflow->creator);

        Deal::open()->whereHas($this->relation, function ($query) {
            $query->where($this->model->getKeyName(), $this->model->getKey());
        })->get()->each(function (Deal $deal) {
            $deal->broadcastToCurrentUser()->markAsLost($this->lost_reason);
        });

        ChangeLogger::setCauser(null);
    }

    /**
     * Action available fields
     */
    public function fields(): array
    {
        return [
            LostReasonField::make('lost_reason', __('deals::deal.workflows.actions.fields.lost_reason')),
        ];
    }
}
