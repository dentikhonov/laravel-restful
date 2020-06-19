<?php


namespace Devolt\Restful\Http\Responses;


use Devolt\Restful\Http\Responses\Traits\WithRelations;

class JsonApiResourceCollection /* extends ResourceCollection */
{
    use WithRelations;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => JsonApiResource::collection($this->collection),
        ];
    }

    public function with($request)
    {
        return [
            'included' => $this->withIncluded($request)
        ];
    }

    public function getRelations(): array
    {
        return $this->collection
            ->map(fn(JsonResource $resource) => $resource->getRelations())
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }
}
