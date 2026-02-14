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

namespace Modules\Users\Services;

use Modules\Core\Models\DataView;
use Modules\Core\Models\Workflow;
use Modules\Users\Events\TransferringUserData;
use Modules\Users\Models\Team;
use Modules\Users\Models\User;

class TransferUserDataService
{
    protected int $fromUserId;

    /**
     * Create new TransferUserData instance.
     */
    public function __construct(protected int $toUserId, protected User $fromUser)
    {
        $this->fromUserId = $fromUser->getKey();
    }

    /**
     * Invoke the transfer
     */
    public function __invoke(): void
    {
        TransferringUserData::dispatch($this->toUserId, $this->fromUserId, $this->fromUser);

        $this->transferSharedViews();
        $this->transferTeams();
        $this->transferWorkflows();
    }

    /**
     * Transfer user being deleted shared views.
     */
    public function transferSharedViews(): void
    {
        DataView::where('user_id', $this->fromUserId)->shared()->update(['user_id' => $this->toUserId]);
    }

    /**
     * Transfer created workflows.
     */
    public function transferWorkflows(): void
    {
        Workflow::where('created_by', $this->fromUserId)->update(['created_by' => $this->toUserId]);
    }

    /**
     * Transfer teams.
     */
    public function transferTeams(): void
    {
        Team::where('user_id', $this->fromUserId)->update(['user_id' => $this->toUserId]);
    }
}
