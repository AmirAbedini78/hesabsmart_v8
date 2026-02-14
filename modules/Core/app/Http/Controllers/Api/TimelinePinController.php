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

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Core\Common\Timeline\Timeline;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Models\PinnedTimelineSubject;

class TimelinePinController extends ApiController
{
    /**
     * Pin the given timelineable to the given resource.
     */
    public function store(Request $request): void
    {
        $data = $this->validateRequest($request);

        (new PinnedTimelineSubject)->pin(...$data);
    }

    /**
     * Unpin the given timelineable to the given resource.
     */
    public function destroy(Request $request): void
    {
        $data = $this->validateRequest($request);

        (new PinnedTimelineSubject)->unpin(...$data);
    }

    /**
     * Validate the request.
     */
    protected function validateRequest(Request $request): array
    {
        $data = $request->validate([
            'subject_id' => 'required|int',
            'subject_type' => 'required|string',
            'timelineable_id' => 'required|int',
            'timelineable_type' => 'required|string',
        ]);

        $subject = Timeline::getPinableSubject($data['subject_type']);
        $timelineable = Timeline::getSubjectAcceptedTimelineable($data['subject_type'], $data['timelineable_type']);

        abort_if(is_null($subject) || is_null($timelineable), 404);

        return [
            $data['subject_id'],
            $subject['subject'],
            $data['timelineable_id'],
            $timelineable['timelineable_type'],
        ];
    }
}
