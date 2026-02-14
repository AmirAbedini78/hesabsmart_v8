<?php

namespace Tests\Fixtures;

use Modules\Core\Resource\JsonResource;

class EventJsonResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
        ], $request);
    }
}
