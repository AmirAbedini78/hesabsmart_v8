<?php

namespace Tests\Fixtures;

use Modules\Core\Http\Resources\JsonResource;

class LocationJsonResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Modules\Core\Http\Requests\ResourceRequest  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'location_type' => $this->location_type,
        ];
    }
}
