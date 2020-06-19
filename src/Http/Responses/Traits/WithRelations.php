<?php

namespace Devolt\Restful\Http\Responses\Traits;

use Devolt\Restful\Models\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

/**
 * @mixin JsonResource
 */
trait WithRelations
{
    protected function mapDataRelations($relations = null)
    {
        return collect($relations ?? $this->getRelations())
            // ->filter(fn($relation) => $relation instanceof Collection)
            ->map(fn($relation) => $this->mapSingleRelation($relation))
            ->all();
    }

    /**
     * @param $relation
     * @return array
     * @throws ReflectionException todo
     */
    protected function mapSingleRelation($relation): array
    {
        if ($relation instanceof Collection) {
            return [
                'data' => collect($relation)->map(fn($item) => [
                    'type' => $this->getType($item),
                    'id' => $item->getKey(),
                    'meta' => $this->mapRelationMeta($item)
                ])
            ];
        }

        return [
            'data' => [
                'type' => $this->getType($relation),
                'id' => $relation->getKey(),
            ]
        ];
    }

    /**
     * @param Model $relation
     * @return array
     */
    protected function mapRelationMeta(Model $relation): array
    {
        if ($relation->relationLoaded('pivot')) {
            /** @var Pivot $pivot */
            $pivot = $relation->getRelation('pivot');
            return collect($pivot)->all();
        }

        return [];
    }

    /**
     * @param Request $request
     * @param Model[]|Collection $relations
     * @return array
     */
    protected function withIncluded($request, $relations = null): array
    {
        return collect($relations ?? $this->getRelations())
            ->flatten()
            ->map(fn(Model $item) => $this->getResourceFor($item)->toArray($request))
            ->all();
    }

    /**
     * @param Model $item
     * @return JsonResource
     */
    protected function getResourceFor(Model $item)
    {
        $resource = $item->getResource();
        return new $resource($item);
    }

    public function getRelations(): array
    {
        return collect($this->resource->getRelations())
            ->filter(fn ($relation, $key) => !($relation instanceof Pivot) && ($key !== 'pivot'))
            ->all();
    }

    /**
     * @param mixed|Model $model
     * @return string
     * @throws ReflectionException
     */
    protected function getType($model): string
    {
        return Str::kebab(Str::plural((new ReflectionClass($model))->getShortName())); //todo
    }
}
