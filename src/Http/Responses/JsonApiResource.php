<?php

namespace Devolt\Restful\Http\Responses;

use Devolt\Restful\Http\Responses\Traits\WithRelations;
use Devolt\Restful\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use ReflectionException;

/**
 * @mixin Model
 */

class JsonApiResource extends JsonResource
{
    use WithRelations;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @throws ReflectionException //todo
     */
    public function toArray($request)
    {
        return [
            'type' => $this->getType($this->resource),
            'id' => $this->getKey(),
            'attributes' => collect($this->attributesToArray())->except(['id']),
            'relationships' => $this->mapDataRelations(),
        ];
    }

    public function with($request)
    {
        return [
            'included' => $this->withIncluded($request)
        ];
    }
}
