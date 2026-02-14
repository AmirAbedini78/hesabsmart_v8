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

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Core\Contracts\Calendar\DisplaysOnCalendar */
class CalendarEventResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'title' => $this->getCalendarTitle($request->viewName),
            'start' => $this->getCalendarStartDate($request->viewName),
            'end' => $this->getCalendarEndDate($request->viewName),
            'allDay' => $this->isAllDay(),
            'readonly' => $request->user()->cant('update', $this->resource),
            'extendedProps' => array_merge([
                'event_type' => strtolower(class_basename($this->resource)),
            ], method_exists($this->resource, 'getCalendarExtendedProps') ?
            $this->getCalendarExtendedProps() :
            []),
        ];
    }
}
