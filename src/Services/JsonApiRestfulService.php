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
}
