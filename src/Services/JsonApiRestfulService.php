<?php

namespace Devolt\Restful\Services;

use Devolt\Restful\Contracts\Restful;
use Devolt\Restful\Http\Responses\JsonApiResource;
use Devolt\Restful\Http\Responses\JsonApiResourceCollection;
use Devolt\Restful\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JsonApiRestfulService extends BaseRestfulService implements Restful
{
    protected Request $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function getPerPage(): ?int
    {
        return $this->request->query('per_page', parent::getPerPage());
    }

    /**
     * @inheritDoc
     */
    protected function getAttributesFromData(array $data): array
    {
        return $data['data']['attributes'];
    }

    /**
     * @inheritDoc
     */
    public function getResourceClass(): string
    {
        return $this->getModelInstance()->getResource() ?? JsonApiResource::class;
    }

    /**
     * @inheritDoc
     */
    public function getResourceCollectionClass(): string
    {
        return $this->getModelInstance()->getResourceCollection() ?? JsonApiResourceCollection::class;
    }

    /**
     * @inheritDoc
     */
    public function collectionQuery(): Builder
    {
        $query = $this->qualifyCollectionPolicyQuery($this->getModelInstance()->newModelQuery());
        $query = $this->qualifyCollectionRelationsQuery($query);
        // todo Добавить поддержку Builder сс #2
        // $query = $this->qualifyQueryBuilder($query);

        return $query;
    }

    /**
     * @var Model $model
     * @inheritDoc
     */
    public function singleItemQuery($model): Model
    {
        $model->load($model::getItemWith());
        $model->loadCount($model::getItemWithCount());

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function createInstance(array $input): Model
    {
        return $this->getModelInstance()->create($this->validateResource($this->getModelInstance(), $input));
    }

    /**
     * @inheritDoc
     */
    public function updateInstance(Model $model, array $input): Model
    {
        $model->update($this->validateResourceUpdate($model, $input));

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function deleteInstance(Model $model)
    {
        return $model->delete();
    }
}
