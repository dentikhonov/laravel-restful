<?php

namespace Devolt\Restful\Services;

use Devolt\Restful\Contracts\Restful;
use Devolt\Restful\Http\Responses\JsonApiResource;
use Devolt\Restful\Http\Responses\JsonApiResourceCollection;
use Devolt\Restful\Models\Model;
use Devolt\Restful\Models\Traits\WithQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

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
    public function getAttributesFromData(array $data): array
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
    protected function qualifyQueryBuilder($query): Builder
    {
        return QueryBuilder::for($query)
            ->allowedFilters($this->getModelInstance()->getAllowedFilters())
            ->defaultSorts($this->getModelInstance()->getDefaultSorts())
            ->allowedSorts($this->getModelInstance()->getAllowedSorts())
            ->allowedFields($this->getModelInstance()->getAllowedFields())
            ->allowedIncludes($this->getModelInstance()->getAllowedIncludes())
            ->allowedAppends($this->getModelInstance()->getAllowedAppends());
    }

    public function collectionQuery(): Builder
    {
        $query = parent::collectionQuery();

        if (in_array(WithQueryBuilder::class, class_uses($this->getModelInstance()))) {
            $query = $this->qualifyQueryBuilder($query);
        }

        return $query;
    }
}
