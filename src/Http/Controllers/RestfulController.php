<?php

namespace Devolt\Restful\Http\Controllers;

use Devolt\Restful\Models\Model;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RestfulController extends BaseRestfulController
{
    /**
     * List all entities.
     *
     * @return Response
     */
    public function index(): Response
    {
        $query = $this->restfulService->collectionQuery();
        $perPage = $this->restfulService->getPerPage();
        $resourceClass = $this->getResourceCollectionClass();

        /** @var ResourceCollection $response */
        $response = new $resourceClass($perPage ? $query->paginate($perPage) : $query->get());
        return $response->response();
    }

    /**
     * Get single entity.
     *
     * @param Model $model Requested resource.
     * @return Response
     */
    public function get(Model $model): Response
    {
        $resource = $this->restfulService->singleItemQuery($model);
        $resourceClass = $this->getResourceClass();

        /** @var JsonResource $response */
        $response = new $resourceClass($resource);
        return $response->response();
    }

    /**
     * Create single entity.
     *
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function post(Request $request): Response
    {
        $model = $this->restfulService->createInstance($request->input());
        $resource = $this->restfulService->singleItemQuery($model);
        $resourceClass = $this->getResourceClass();

        /** @var JsonResource $response */
        $response = new $resourceClass($resource);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param Model $model Requested resource.
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function patch(Model $model, Request $request): Response
    {
        $model = $this->restfulService->updateInstance($model, $request->input());
        $resource = $this->restfulService->singleItemQuery($model);
        $resourceClass = $this->getResourceClass();

        /** @var JsonResource $response */
        $response = new $resourceClass($resource);
        return $response->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * @param Model $model Requested resource.
     * @return Response
     * @throws Exception
     */
    public function delete(Model $model): Response
    {
        $this->restfulService->deleteInstance($model);

        return response()->json(null)->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
