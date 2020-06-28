<?php

namespace Devolt\Restful\Http\Controllers;

use Devolt\Restful\Contracts\Restful;
use Devolt\Restful\Models\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Controller as BaseController;

abstract class BaseRestfulController extends BaseController
{
    use AuthorizesRequests;

    /** @var string<Model> Target model class */
    protected static string $model = Model::class;

    /** @var string<JsonResource>|null */
    protected static ?string $resource = null;

    /** @var string<ResourceCollection>|null */
    protected static ?string $resourceCollection = null;

    /** @var Restful $restfulService */
    protected Restful $restfulService;

    /**
     * @param Restful $restfulService
     */
    public function __construct(Restful $restfulService)
    {
        if (method_exists($this, 'authorizeResource')) {
            $this->authorizeResource(static::$model);
        }

        $this->restfulService = $restfulService;
        $this->restfulService->setModel(static::$model);
    }

    /**
     * Get the map of resource methods to ability names.
     *
     * @return array
     */
    protected function resourceAbilityMap()
    {
        return [
            'index' => 'viewAny',
            'get' => 'view',
            'post' => 'create',
            'patch' => 'update',
            'delete' => 'delete',
        ];
    }

    /**
     * Get the list of resource methods which do not have model parameters.
     *
     * @return array
     */
    protected function resourceMethodsWithoutModels()
    {
        return ['index', 'post',];
    }

    protected function getResourceClass(): string
    {
        if (!is_null(static::$resource)) {
            return static::$resource;
        }

        return $this->restfulService->getResourceClass();
    }

    protected function getResourceCollectionClass(): string
    {
        if (!is_null(static::$resourceCollection)) {
            return static::$resourceCollection;
        }

        return $this->restfulService->getResourceCollectionClass();
    }
}
